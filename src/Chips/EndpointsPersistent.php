<?php
/**
 * Endpoints that static and persistent
 * User: moyo
 * Date: 2018/5/16
 * Time: 3:05 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Net\Endpoint;

trait EndpointsPersistent
{
    /**
     * @var Endpoint[]
     */
    private $staticEndpoints = [];

    /**
     * @param Endpoint ...$endpoints
     * @return static
     */
    public function addEndpoint(Endpoint ...$endpoints) : self
    {
        foreach ($endpoints as $endpoint) {
            isset($this->staticEndpoints[$i = $endpoint->id()]) || $this->staticEndpoints[$i] = $endpoint;
        }

        return $this;
    }

    /**
     * @return Endpoint
     */
    protected function staticEndpoint() : Endpoint
    {
        return $this->staticEndpoints[array_rand($this->staticEndpoints)];
    }

    /**
     * @return array
     */
    protected function staticEndpoints() : array
    {
        return $this->staticEndpoints;
    }
}
