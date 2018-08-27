<?php
/**
 * Daemon base
 * User: moyo
 * Date: 2018/7/6
 * Time: 2:26 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Promise\Promised;

abstract class Daemon
{
    /**
     * @return Promised
     */
    abstract protected function background() : Promised;
}
