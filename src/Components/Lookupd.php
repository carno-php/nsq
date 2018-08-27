<?php
/**
 * Lookupd
 * User: moyo
 * Date: 15/11/2017
 * Time: 4:02 PM
 */

namespace Carno\NSQ\Components;

use Carno\HTTP\Client;
use Carno\HTTP\Options;
use Carno\HTTP\Standard\Request;
use Carno\HTTP\Standard\Response;
use Carno\HTTP\Standard\Uri;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use Carno\NSQ\Chips\LookupCached;
use Carno\NSQ\Exception\LookupRequestException;
use Carno\NSQ\Exception\NoneEndpointsException;
use Closure;

class Lookupd
{
    use LookupCached;

    /**
     * @var string
     */
    private $host = null;

    /**
     * @var int
     */
    private $port = null;

    /**
     * @var Client
     */
    private $http = null;

    /**
     * @var array
     */
    private $observers = [];

    /**
     * Lookupd constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host = 'localhost', int $port = 4161)
    {
        $this->host = $host;
        $this->port = $port;

        $this->http = new Client(new Options, new Address($host, $port));
    }

    /**
     * @param string $topic
     * @param Closure $observer
     * @return Endpoint[]
     * @throws NoneEndpointsException
     */
    public function endpoints(string $topic, Closure $observer = null)
    {
        $this->observers[] = $observer;

        $querying = function () use ($topic) {
            /**
             * @var Response $response
             */
            $uri = new Uri('http', $this->host, $this->port, '/lookup', ['topic' => $topic]);
            $request = new Request('GET', $uri, ['Accept' => 'application/vnd.nsq; version=1.0']);
            $response = yield $this->http->perform($request);
            switch ($response->getStatusCode()) {
                case 200:
                    $endpoints = $this->parsing((string)$response->getBody());
                    if ($this->observers) {
                        foreach ($this->observers as $observer) {
                            yield $observer($endpoints);
                        }
                    }
                    return $endpoints;
                case 404:
                    throw new NoneEndpointsException;
                default:
                    throw new LookupRequestException;
            }
        };

        if ($this->cached) {
            return yield $this->cached->delegate($topic, $querying, $this->ttl);
        } else {
            return yield $querying();
        }
    }

    /**
     * @param string $body
     * @return Endpoint[]
     */
    private function parsing(string $body) : array
    {
        $response = json_decode($body, true);
        if (isset($response['producers']) && $response['producers']) {
            $eps = [];
            foreach ($response['producers'] as $producer) {
                $eps[] =
                    (new Endpoint(new Address($producer['broadcast_address'], $producer['tcp_port'])))
                        ->relatedService($producer['hostname'])
                ;
            }
            return $eps;
        }
        throw new NoneEndpointsException;
    }
}
