<?php
/**
 * Normally nsqd daemon API
 * User: moyo
 * Date: 2018/7/6
 * Time: 12:03 PM
 */

namespace Carno\NSQ\Chips;

use function Carno\Coroutine\all;
use Carno\NSQ\Connector\Linker;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

trait N2Daemon
{
    /**
     * @var Promised
     */
    private $exited = null;

    /**
     * @see startup
     * @deprecated
     * @return Promised
     */
    public function daemon() : Promised
    {
        return $this->startup();
    }

    /**
     * @return Promised
     */
    private function exited() : Promised
    {
        return $this->exited ?? $this->exited = Promise::deferred();
    }

    /**
     * @return Promised
     */
    public function startup() : Promised
    {
        return all($this->background(), $this->exited());
    }

    /**
     * @return Promised
     */
    public function shutdown() : Promised
    {
        $waits = [];

        $this->linkers(function (Linker $linker) use (&$waits) {
            array_push($waits, $linker->disconnect());
        });

        return all(...$waits)->sync($this->exited());
    }
}
