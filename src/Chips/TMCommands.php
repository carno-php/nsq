<?php
/**
 * Type message commands
 * User: moyo
 * Date: 02/03/2018
 * Time: 2:40 PM
 */

namespace Carno\NSQ\Chips;

use Carno\NSQ\Connector\Nsqd;

trait TMCommands
{
    /**
     * @var Nsqd
     */
    private $link = null;

    /**
     * @param Nsqd $nsqd
     * @return static
     */
    public function link(Nsqd $nsqd) : self
    {
        $this->link = $nsqd;
        return $this;
    }

    /**
     */
    public function done() : void
    {
        $this->link->fin($this->id());
    }

    /**
     * delayed in milliseconds
     * @param int $delayed
     */
    public function retry(int $delayed = 0) : void
    {
        $this->link->req($this->id(), $delayed);
    }
}
