<?php
/**
 * Endpoints refresher (from lookupd)
 * User: moyo
 * Date: 17/11/2017
 * Time: 5:29 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Net\Endpoint;
use Carno\NSQ\Components\Lookupd;
use Carno\NSQ\Connector\Linker;
use Closure;

trait EndpointsRefresher
{
    /**
     * @var Endpoint[]
     */
    private $cachedEndpoints = [];

    /**
     * @param Lookupd $lookupd
     * @param string $topic
     * @return Endpoint
     */
    protected function lookupEndpoint(Lookupd $lookupd, string $topic)
    {
        if (empty($this->cachedEndpoints)) {
            $this->cachedEndpoints = yield $lookupd->endpoints($topic, function (array $endpoints) {
                $this->cachedEndpoints = $endpoints;
            });
        }

        return $this->cachedEndpoints[array_rand($this->cachedEndpoints)];
    }

    /**
     * @param string $topic
     * @param Closure $linker
     * @param Lookupd $lookupd
     * @param array $statics
     * @return Endpoint[]
     */
    protected function syncedEndpoints(string $topic, Closure $linker, Lookupd $lookupd = null, array $statics = [])
    {
        return $lookupd ? $lookupd->endpoints($topic, function (array $endpoints) use ($linker) {
            return $this->routedEndpoints($linker, ...$endpoints);
        }) : $this->routedEndpoints($linker, ...$statics);
    }

    /**
     * @param Closure $linker
     * @param Endpoint ...$endpoints
     * @return Endpoint[]
     */
    private function routedEndpoints(Closure $linker, Endpoint ...$endpoints)
    {
        // marking
        $current = $this->markedEndpoints(...$this->cachedEndpoints);
        $found = $this->markedEndpoints(...$endpoints);

        // reset
        $this->cachedEndpoints = $endpoints;

        // checking
        foreach ($current as $id => $endpoint) {
            if (isset($found[$id])) {
                unset($found[$id]);
                unset($current[$id]);
            }
        }

        // new joining
        foreach ($found as $endpoint) {
            yield $linker(Linker::ACT_JOIN, $endpoint);
        }

        // old leaving
        foreach ($current as $endpoint) {
            yield $linker(Linker::ACT_LEAVE, $endpoint);
        }

        return $endpoints;
    }

    /**
     * @param Endpoint ...$endpoints
     * @return Endpoint[]
     */
    private function markedEndpoints(Endpoint ...$endpoints)
    {
        $marked = [];

        foreach ($endpoints as $endpoint) {
            $marked[(string)$endpoint->address()] = $endpoint;
        }

        return $marked;
    }
}
