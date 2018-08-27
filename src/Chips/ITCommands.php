<?php
/**
 * Interactive commands
 * User: moyo
 * Date: 26/02/2018
 * Time: 5:18 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Promise\Promised;

trait ITCommands
{
    /**
     * @param array $options
     * @return Promised
     */
    public function identify(array $options) : Promised
    {
        $j = json_encode($options);
        $w = $this->waiting();
        $this->sending("IDENTIFY\n" . pack('N', strlen($j)) . $j);
        return $w;
    }

    /**
     * @param string $topic
     * @param string ...$messages
     * @return Promised
     */
    public function pub(string $topic, string ...$messages) : Promised
    {
        $w = $this->waiting();
        if (count($messages) > 1) {
            // multi pub
            $mn = pack('N', count($messages));
            $ml = 0;
            $ms = '';
            foreach ($messages as $message) {
                $ml += strlen($message) + 4;
                $ms .= pack('N', strlen($message)) . $message;
            }
            $this->sending("MPUB {$topic}\n" . pack('N', $ml + 4) . $mn . $ms);
        } else {
            // single pub
            $this->sending("PUB {$topic}\n" . pack('N', strlen($messages[0])) . $messages[0]);
        }
        return $w;
    }

    /**
     * @param string $topic
     * @param string $message
     * @param int $defer
     * @return Promised
     */
    public function dpub(string $topic, string $message, int $defer) : Promised
    {
        $w = $this->waiting();
        $this->sending("DPUB {$topic} {$defer}\n" . pack('N', strlen($message)) . $message);
        return $w;
    }

    /**
     * @param string $topic
     * @param string $channel
     * @return Promised
     */
    public function sub(string $topic, string $channel) : Promised
    {
        $w = $this->waiting();
        $this->sending("SUB {$topic} {$channel}\n");
        return $w;
    }

    /**
     * @param int $count
     */
    public function rdy(int $count) : void
    {
        $this->sending("RDY {$count}\n");
    }

    /**
     * @return Promised
     */
    public function cls() : Promised
    {
        $w = $this->waiting();
        $this->sending("CLS\n");
        return $w;
    }

    /**
     * @param string $id
     */
    public function fin(string $id) : void
    {
        $this->sending("FIN {$id}\n");
    }

    /**
     * @param string $id
     * @param int $defer
     */
    public function req(string $id, int $defer = 0) : void
    {
        $this->sending("REQ {$id} {$defer}\n");
    }

    /**
     * @param string $id
     */
    public function touch(string $id) : void
    {
        $this->sending("TOUCH {$id}\n");
    }

    /**
     */
    public function nop() : void
    {
        $this->sending("NOP\n");
    }
}
