<?php
/**
 * Lookupd connector
 * User: moyo
 * Date: 15/11/2017
 * Time: 3:43 PM
 */

namespace Carno\NSQ\Chips;

use Carno\NSQ\Components\Lookupd;

trait LookupAssigned
{
    /**
     * @var Lookupd
     */
    private $lookupd = null;

    /**
     * @param Lookupd $lookupd
     * @return static
     */
    public function setLookupd(Lookupd $lookupd) : self
    {
        $this->lookupd = $lookupd;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLookupd() : bool
    {
        return ! is_null($this->lookupd);
    }

    /**
     * @return Lookupd
     */
    public function getLookupd() : Lookupd
    {
        return $this->lookupd;
    }
}
