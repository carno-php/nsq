<?php
/**
 * Frame processor
 * User: moyo
 * Date: 26/02/2018
 * Time: 5:31 PM
 */

namespace Carno\NSQ\Protocol;

use Carno\NSQ\Connector\Nsqd;
use Carno\NSQ\Exception\ServerException;
use Carno\NSQ\Types\Consuming;
use Carno\Promise\Promised;
use Closure;

class Receiver
{
    // in receiving
    private const STA_RECV = 1;

    // sta cleaning
    private const STA_CLEAR = 0;

    /**
     * @var int
     */
    private $state = self::STA_CLEAR;

    /**
     * @var Buffer
     */
    private $buffer = null;

    /**
     * @var int
     */
    private $sized = 0;

    /**
     * Framing constructor.
     */
    public function __construct()
    {
        $this->buffer = new Buffer;
    }

    /**
     * @param string $recv
     * @param Nsqd $nsqd
     * @param Closure $waiter
     * @param Consuming $consuming
     */
    public function inbound(string $recv, Nsqd $nsqd, Closure $waiter, Consuming $consuming = null) : void
    {
        $this->buffer->write($recv);

        PARSE_LOOP:

        switch ($this->state) {
            case self::STA_RECV:
                break;
            case self::STA_CLEAR:
                $this->sized = Binary::int($this->buffer);
                $this->state = self::STA_RECV;
                break;
        }

        /**
         * @var Promised $waiting
         */

        if ($this->buffer->size() >= $this->sized) {
            $waiting = $waiter();
            $this->state = self::STA_CLEAR;
            $frame = new Frame($this->sized, $this->buffer);
            switch (1) {
                case $frame->isResponse():
                    switch (1) {
                        case $frame->isOK():
                            $waiting->resolve(true);
                            break;
                        case $frame->isHeartbeat():
                            $nsqd->nop();
                            break;
                        case $frame->isCloseWait():
                            $waiting->resolve();
                            $nsqd->close();
                            break;
                    }
                    break;
                case $frame->isMessage():
                    $consuming->invoking($nsqd, $frame->getMessage());
                    break;
                case $frame->isError():
                    $waiting->pended() && $waiting->throw(new ServerException($frame->getError()));
                    break;
            }

            if ($this->buffer->valid()) {
                goto PARSE_LOOP;
            }
        }
    }
}
