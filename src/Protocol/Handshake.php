<?php
/**
 * NSQd handshake
 * User: moyo
 * Date: 15/11/2017
 * Time: 3:47 PM
 */

namespace Carno\NSQ\Protocol;

use Carno\NSQ\Connector\Nsqd;
use Carno\Promise\Promised;
use Carno\Socket\Contracts\Stream;

class Handshake
{
    /**
     * magic identify
     */
    private const MAGIC = '  V2';

    /**
     * Handshake constructor.
     * @param Stream $conn
     * @param Nsqd $nsqd
     * @param Promised $connected
     */
    public function __construct(Stream $conn, Nsqd $nsqd, Promised $connected)
    {
        $options = [
            'client_id' => sprintf('%d', posix_getpid()),
            'hostname' => gethostname(),
            'user_agent' => 'carno-nsq/1.0'
        ];

        $conn->send(self::MAGIC);

        $nsqd->identify($options)->sync($connected);
    }
}
