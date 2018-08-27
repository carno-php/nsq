<?php
/**
 * Lookup cached
 * User: moyo
 * Date: 15/11/2017
 * Time: 5:22 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Cache\Adaptors\Memory;

trait LookupCached
{
    /**
     * @var Memory
     */
    private $cached = null;

    /**
     * @var int
     */
    private $ttl = null;

    /**
     * @param Memory $storing
     * @param int $ttl
     * @return static
     */
    public function setCaching(Memory $storing, int $ttl = 60) : self
    {
        $this->cached = $storing;
        $this->ttl = $ttl;
        return $this;
    }
}
