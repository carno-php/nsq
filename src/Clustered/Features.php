<?php
/**
 * Clustered features
 * User: moyo
 * Date: 2018/7/9
 * Time: 11:26 AM
 */

namespace Carno\NSQ\Clustered;

use function Carno\Coroutine\all;
use Carno\DSN\DSN;
use Carno\Net\Endpoint;
use Carno\NSQ\Components\Lookupd;
use Carno\NSQ\Consumer;
use Carno\NSQ\Exception\ClusterEndpointInitException;
use Carno\NSQ\Producer;
use Carno\NSQ\Types\Consuming;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

class Features
{
    /**
     * @var Producer
     */
    private $producer = null;

    /**
     * @var Consumer
     */
    private $consumer = null;

    /**
     * Features constructor.
     * @param string $topic
     * @param string $channel
     * @param Endpoint $endpoint
     * @param Promised $subscribed
     * @param int $producing
     * @param Consuming $consuming
     */
    public function __construct(
        string $topic,
        string $channel,
        Endpoint $endpoint,
        Promised $subscribed,
        int $producing = null,
        Consuming $consuming = null
    ) {
        $host = $endpoint->address()->host();
        $port = $endpoint->address()->port();

        if ($port <= 0) {
            $dsn = new DSN($host);
            $host = $dsn->host();
            $port = $dsn->port();
            switch ($dsn->scheme()) {
                case 'lookupd':
                    $lookupd = new Lookupd($host, $port);
                    break;
                case 'nsqd':
                    $nsqd = $endpoint;
                    break;
                default:
                    throw new ClusterEndpointInitException('Not supported scheme');
            }
        } else {
            $lookupd = new Lookupd($host, $port);
        }

        $producing === null || $this->producer = new Producer($producing);
        $consuming === null || $this->consumer = new Consumer($consuming);

        if (isset($lookupd)) {
            $this->producer && $this->producer->setLookupd($lookupd);
            $this->consumer && $this->consumer->setLookupd($lookupd);
        }

        if (isset($nsqd)) {
            $this->producer && $this->producer->addEndpoint($nsqd);
            $this->consumer && $this->consumer->addEndpoint($nsqd);
        }

        if ($this->producer) {
            $this->producer->setTopic($topic)->startup();
        }

        if ($this->consumer) {
            $this->consumer->setTopic($topic)->setChannel($channel);
            $subscribed->then(function () {
                $this->consumer->startup();
            });
        }
    }

    /**
     * @return Producer
     */
    public function producer() : ?Producer
    {
        return $this->producer;
    }

    /**
     * @return Consumer
     */
    public function consumer() : ?Consumer
    {
        return $this->consumer;
    }

    /**
     * @return Promised
     */
    public function shutdown() : Promised
    {
        return all(
            $this->producer ? $this->producer->shutdown() : Promise::resolved(),
            $this->consumer ? $this->consumer->shutdown() : Promise::resolved()
        );
    }
}
