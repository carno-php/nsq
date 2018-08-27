<?php
/**
 * Interactive channel
 * User: moyo
 * Date: 17/11/2017
 * Time: 3:41 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Channel\Chan;
use Carno\Channel\Channel;

trait InteractiveChan
{
    /**
     * @var Chan
     */
    private $chan = null;

    /**
     * @return Chan
     */
    private function chan() : Chan
    {
        return $this->chan;
    }

    /**
     * @param int $queued
     */
    private function init(int $queued) : void
    {
        $queued && $this->chan = new Channel($queued);
    }

    /**
     * @return bool
     */
    private function queued() : bool
    {
        return !! $this->chan;
    }
}
