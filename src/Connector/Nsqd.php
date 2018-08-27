<?php
/**
 * Nsqd core
 * User: moyo
 * Date: 17/11/2017
 * Time: 6:36 PM
 */

namespace Carno\NSQ\Connector;

use Carno\Net\Contracts\TCP;
use Carno\Net\Endpoint;
use Carno\Net\Events;
use Carno\NSQ\Chips\ITCommands;
use Carno\NSQ\Protocol\Handshake;
use Carno\NSQ\Protocol\Receiver;
use Carno\NSQ\Protocol\Subscribe;
use Carno\NSQ\Types\Consuming;
use Carno\Pool\Managed;
use Carno\Pool\Poolable;
use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Carno\Socket\Contracts\Stream;
use Carno\Socket\Socket;

class Nsqd implements Poolable
{
    use Managed;
    use ITCommands;

    /**
     * @var TCP
     */
    private $socket = null;

    /**
     * @var Endpoint
     */
    private $endpoint = null;

    /**
     * @var Consuming
     */
    private $consuming = null;

    /**
     * @var Promised
     */
    private $connected = null;

    /**
     * @var Promised
     */
    private $disconnected = null;

    /**
     * @var Promised
     */
    private $waiting = null;

    /**
     * @var Receiver
     */
    private $receiver = null;

    /**
     * Nsqd constructor.
     * @param Endpoint $endpoint
     * @param Consuming $consuming
     */
    public function __construct(Endpoint $endpoint, Consuming $consuming = null)
    {
        $this->receiver = new Receiver;

        $this->endpoint = $endpoint;
        $this->consuming = $consuming;

        $this->connected = Promise::deferred();
        $this->disconnected = Promise::deferred()->sync($this->closed());
    }

    /**
     * @return Promised
     */
    public function connect() : Promised
    {
        $this->socket = Socket::connect($this->endpoint->address(), $this->protocol());
        return $this->connected;
    }

    /**
     * @return Promised
     */
    public function heartbeat() : Promised
    {
        return Promise::resolved();
    }

    /**
     * @return Promised
     */
    public function close() : Promised
    {
        $this->consuming ? $this->cls() : $this->socket->close();
        return $this->disconnected;
    }

    /**
     * @return Promised
     */
    protected function waiting() : Promised
    {
        return $this->waiting = Promise::deferred();
    }

    /**
     * @param string $data
     * @return bool
     */
    protected function sending(string $data) : bool
    {
        return $this->socket->send($data);
    }

    /**
     * @return Events
     */
    private function protocol() : Events
    {
        return (new Events)
            ->attach(Events\Socket::CONNECTED, function (Stream $conn) {
                new Handshake($conn, $this, $this->connected);
                $this->consuming && new Subscribe($this->connected, $this, $this->consuming);
            })
            ->attach(Events\Socket::RECEIVED, function (Stream $conn) {
                $this->receiver->inbound($conn->recv(), $this, function () {
                    return $this->waiting;
                }, $this->consuming);
            })
            ->attach(Events\Socket::CLOSED, function () {
                $this->disconnected->resolve();
            })
            ->attach(Events\Socket::ERROR, function () {
                $this->socket && $this->socket->close();
                $this->destroy();
            })
        ;
    }
}
