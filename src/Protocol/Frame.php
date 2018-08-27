<?php
/**
 * Type frame
 * User: moyo
 * Date: 27/02/2018
 * Time: 2:32 PM
 */

namespace Carno\NSQ\Protocol;

use Carno\NSQ\Types\Message;

class Frame
{
    // types
    private const TYPE_RESPONSE = 0;
    private const TYPE_ERROR = 1;
    private const TYPE_MESSAGE = 2;

    // responses
    private const RESP_OK = 'OK';
    private const RESP_HEARTBEAT = '_heartbeat_';
    private const RESP_CLOSE_WAIT = 'CLOSE_WAIT';

    /**
     * @var string
     */
    private $response = null;

    /**
     * @var string
     */
    private $error = null;

    /**
     * @var Message
     */
    private $message = null;

    /**
     * Frame constructor.
     * @param int $sized
     * @param Buffer $buffer
     */
    public function __construct(int $sized, Buffer $buffer)
    {
        switch (Binary::int($buffer)) {
            case self::TYPE_RESPONSE:
                $this->response = Binary::string($buffer, $sized - 4);
                break;
            case self::TYPE_ERROR:
                $this->error = Binary::string($buffer, $sized - 4);
                break;
            case self::TYPE_MESSAGE:
                $timestamp = Binary::long($buffer);
                $attempts = Binary::short($buffer);
                $identify = Binary::string($buffer, 16);
                $payload = Binary::string($buffer, $sized - 30);
                $this->message = (new Message($payload))->meta($identify, $attempts, $timestamp);
                break;
            default:
                $this->error = Binary::string($buffer, $sized - 4);
        }
    }

    /**
     * @return bool
     */
    public function isResponse() : bool
    {
        return $this->response !== null;
    }

    /**
     * @return bool
     */
    public function isOK() : bool
    {
        return $this->response === self::RESP_OK;
    }

    /**
     * @return bool
     */
    public function isHeartbeat() : bool
    {
        return $this->response === self::RESP_HEARTBEAT;
    }

    /**
     * @return bool
     */
    public function isCloseWait() : bool
    {
        return $this->response === self::RESP_CLOSE_WAIT;
    }

    /**
     * @return bool
     */
    public function isError() : bool
    {
        return $this->error !== null;
    }

    /**
     * @return string
     */
    public function getError() : string
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function isMessage() : bool
    {
        return $this->message !== null;
    }

    /**
     * @return Message
     */
    public function getMessage() : Message
    {
        return $this->message;
    }
}
