<?php
/**
 * Nsqd linker with pool
 * User: moyo
 * Date: 27/02/2018
 * Time: 11:49 AM
 */

namespace Carno\NSQ\Connector;

use Carno\Net\Endpoint;
use Carno\NSQ\Types\Consuming;
use Carno\Pool\Options;
use Carno\Pool\Pool;
use Carno\Pool\Wrapper\SAR;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

class Linker
{
    use SAR;

    // node joining
    public const ACT_JOIN = 0xE1;

    // node leaving
    public const ACT_LEAVE = 0xE9;

    /**
     * @var Pool
     */
    private $pool = null;

    /**
     * @var Nsqd
     */
    private $nsqd = null;

    /**
     * Linker constructor.
     * @param Endpoint $endpoint
     * @param Consuming $consuming
     */
    public function __construct(Endpoint $endpoint, Consuming $consuming = null)
    {
        if (is_null($consuming)) {
            $this->pool = new Pool(new Options, function () use ($endpoint) {
                return new Nsqd($endpoint);
            }, "nsqd:{$endpoint->service()}");
        } else {
            $this->nsqd = new Nsqd($endpoint, $consuming);
        }
    }

    /**
     * @return Promised
     */
    public function connect() : Promised
    {
        return $this->pool ? Promise::resolved() : $this->nsqd->connect();
    }

    /**
     * @return Promised
     */
    public function disconnect() : Promised
    {
        return $this->pool ? $this->pool->shutdown() : $this->nsqd->close();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->sarRun($this->pool, $name, $arguments);
    }
}
