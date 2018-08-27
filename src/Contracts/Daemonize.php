<?php
/**
 * Daemonize API
 * User: moyo
 * Date: 2018/7/6
 * Time: 2:24 PM
 */

namespace Carno\NSQ\Contracts;

use Carno\Promise\Promised;

interface Daemonize
{
    /**
     * @return Promised
     */
    public function startup() : Promised;

    /**
     * @return Promised
     */
    public function shutdown() : Promised;
}
