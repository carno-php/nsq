<?php
/**
 * NSQ consumer
 * User: moyo
 * Date: 15/11/2017
 * Time: 3:40 PM
 */

namespace Carno\NSQ;

use function Carno\Coroutine\async;
use Carno\Net\Endpoint;
use Carno\NSQ\Chips\Daemon;
use Carno\NSQ\Chips\EndpointsPersistent;
use Carno\NSQ\Chips\EndpointsRefresher;
use Carno\NSQ\Chips\INSLinker;
use Carno\NSQ\Chips\LookupAssigned;
use Carno\NSQ\Chips\N2Daemon;
use Carno\NSQ\Chips\TCAssigned;
use Carno\NSQ\Connector\Linker;
use Carno\NSQ\Contracts\Daemonize;
use Carno\NSQ\Types\Consuming;
use Carno\Promise\Promised;

class Consumer extends Daemon implements Daemonize
{
    use N2Daemon, LookupAssigned, TCAssigned, INSLinker;
    use EndpointsRefresher, EndpointsPersistent;

    /**
     * @var Consuming
     */
    private $consuming = null;

    /**
     * Consumer constructor.
     * @param Consuming $consuming
     */
    public function __construct(Consuming $consuming)
    {
        $this->consuming = $consuming;
    }

    /**
     * @return Promised
     */
    protected function background() : Promised
    {
        return async(function () {
            yield $this->syncedEndpoints(
                $this->getTopic(),
                function (int $action, Endpoint $endpoint) {
                    switch ($action) {
                        case Linker::ACT_JOIN:
                            yield $this->linking(
                                $endpoint,
                                $this->consuming->assigned($this->getTopic(), $this->getChannel())
                            )->connect();
                            break;
                        case Linker::ACT_LEAVE:
                            yield $this->linker($endpoint)->disconnect();
                            break;
                    }
                },
                $this->hasLookupd() ? $this->getLookupd() : null,
                $this->hasLookupd() ? [] : $this->staticEndpoints()
            );
        });
    }
}
