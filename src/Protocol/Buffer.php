<?php
/**
 * Recv buffer
 * User: moyo
 * Date: 27/02/2018
 * Time: 12:23 PM
 */

namespace Carno\NSQ\Protocol;

class Buffer
{
    /**
     * @var string
     */
    private $data = '';

    /**
     * @param string $data
     * @return int
     */
    public function write(string $data) : int
    {
        return strlen($this->data .= $data);
    }

    /**
     * @param int $len
     * @return string
     */
    public function read(int $len) : string
    {
        $got = substr($this->data, 0, $len);
        $this->data = substr($this->data, $len);
        return $got;
    }

    /**
     * @return bool
     */
    public function valid() : bool
    {
        return ! $this->eof();
    }

    /**
     * @return int
     */
    public function size() : int
    {
        return strlen($this->data);
    }

    /**
     * @return bool
     */
    public function eof() : bool
    {
        return empty($this->data);
    }
}
