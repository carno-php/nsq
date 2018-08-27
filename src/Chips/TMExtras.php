<?php
/**
 * Type message extras data
 * User: moyo
 * Date: 27/02/2018
 * Time: 2:45 PM
 */

namespace Carno\NSQ\Chips;

trait TMExtras
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var int
     */
    private $attempts = 0;

    /**
     * @var int
     */
    private $timestamp = 0;

    /**
     * @param string $id
     * @param int $attempts
     * @param int $timestamp
     * @return static
     */
    public function meta(string $id, int $attempts, int $timestamp) : self
    {
        $this->id = $id;
        $this->attempts = $attempts;
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function id() : string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function attempts() : int
    {
        return $this->attempts;
    }

    /**
     * @return int
     */
    public function timestamp() : int
    {
        return $this->timestamp;
    }
}
