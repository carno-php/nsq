<?php
/**
 * Type message
 * User: moyo
 * Date: 17/11/2017
 * Time: 3:26 PM
 */

namespace Carno\NSQ\Types;

use Carno\NSQ\Chips\TMCommands;
use Carno\NSQ\Chips\TMExtras;

class Message
{
    use TMExtras, TMCommands;

    /**
     * @var string
     */
    private $payload = null;

    /**
     * Message constructor.
     * @param string $payload
     */
    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function payload() : string
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->payload;
    }
}
