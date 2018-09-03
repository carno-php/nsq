<?php
/**
 * NSQd subscribe
 * User: moyo
 * Date: 02/03/2018
 * Time: 11:47 AM
 */

namespace Carno\NSQ\Protocol;

use function Carno\Coroutine\go;
use Carno\NSQ\Connector\Nsqd;
use Carno\NSQ\Types\Consuming;
use Carno\Promise\Promised;

class Subscribe
{
    /**
     * Subscribe constructor.
     * @param Promised $connected
     * @param Nsqd $nsqd
     * @param Consuming $consuming
     */
    public function __construct(Promised $connected, Nsqd $nsqd, Consuming $consuming)
    {
        go(static function () use ($connected, $nsqd, $consuming) {
            if (yield $connected) {
                if (yield $nsqd->sub($consuming->topic(), $consuming->channel())) {
                    $nsqd->rdy($consuming->concurrency());
                }
            }
        });
    }
}
