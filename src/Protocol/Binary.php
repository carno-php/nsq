<?php
/**
 * Binary ops
 * User: moyo
 * Date: 27/02/2018
 * Time: 2:14 PM
 */

namespace Carno\NSQ\Protocol;

class Binary
{
    /**
     * Read and unpack short (2 bytes) from buffer
     * @param Buffer $buffer
     * @return int
     */
    public static function short(Buffer $buffer) : ?int
    {
        return unpack('n', $buffer->read(2))[1] ?? null;
    }

    /**
     * Read and unpack integer (4 bytes) from buffer
     * @param Buffer $buffer
     * @return int
     */
    public static function int(Buffer $buffer) : ?int
    {
        return ($up = unpack('N', $buffer->read(4))[1] ?? null) ? sprintf('%u', $up) : null;
    }

    /**
     * Read and unpack long (8 bytes) from buffer
     * @param Buffer $buffer
     * @return string
     */
    public static function long(Buffer $buffer) : ?string
    {
        return is_array($hi = unpack('N', $buffer->read(4))) && is_array($lo = unpack('N', $buffer->read(4)))
            ? bcadd(bcmul(sprintf('%u', $hi[1]), '4294967296'), sprintf('%u', $lo[1]))
            : null
        ;
    }

    /**
     * Read raw string from buffer
     * @param Buffer $buffer
     * @param int $size
     * @return string
     */
    public static function string(Buffer $buffer, int $size) : string
    {
        return $buffer->read($size);
    }
}
