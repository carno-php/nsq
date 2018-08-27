<?php
/**
 * NSQ producer
 * User: moyo
 * Date: 15/11/2017
 * Time: 3:40 PM
 */

namespace Carno\NSQ;

use Carno\Channel\Worker;
use function Carno\Coroutine\async;
use Carno\NSQ\Chips\Daemon;
use Carno\NSQ\Chips\EndpointsPersistent;
use Carno\NSQ\Chips\EndpointsRefresher;
use Carno\NSQ\Chips\INSLinker;
use Carno\NSQ\Chips\InteractiveChan;
use Carno\NSQ\Chips\LookupAssigned;
use Carno\NSQ\Chips\N2Daemon;
use Carno\NSQ\Chips\TCAssigned;
use Carno\NSQ\Connector\Nsqd;
use Carno\NSQ\Contracts\Daemonize;
use Carno\NSQ\Types\Message;
use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Throwable;

class Producer extends Daemon implements Daemonize
{
    use N2Daemon, LookupAssigned, TCAssigned, INSLinker;
    use EndpointsRefresher, EndpointsPersistent;
    use InteractiveChan;

    /**
     * Producer constructor.
     * @param int $queued
     */
    public function __construct(int $queued = 0)
    {
        $this->init($queued);
    }

    /**
     * @return Promised
     */
    protected function background() : Promised
    {
        if ($this->queued()) {
            $this->exited()->then(function () {
                $this->chan()->close();
            });

            new Worker($this->chan(), function (array $messages) {
                try {
                    yield $this->sending(...$messages);
                } catch (Throwable $e) {
                    logger('nsq')->info(
                        'Queued publish failure',
                        ['error' => sprintf('%s::%s', get_class($e), $e->getMessage())]
                    );
                }
            });

            return $this->chan()->closed();
        }

        return Promise::resolved();
    }

    /**
     * @param Message ...$messages
     * @return Promised|bool
     */
    public function publish(Message ...$messages) : Promised
    {
        return $this->queued() ? $this->chan()->send($messages) : async($this->sending(...$messages));
    }

    /**
     * @param Message ...$messages
     * @return mixed
     */
    private function sending(Message ...$messages)
    {
        $endpoint = $this->hasLookupd()
            ? yield $this->lookupEndpoint($this->getLookupd(), $this->getTopic())
            : $this->staticEndpoint()
        ;

        /**
         * @var Nsqd $nsq
         */

        $nsq = $this->linking($endpoint);

        return yield $nsq->pub($this->getTopic(), ...$messages);
    }
}
