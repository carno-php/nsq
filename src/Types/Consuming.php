<?php
/**
 * Messages consuming
 * User: moyo
 * Date: 27/02/2018
 * Time: 4:35 PM
 */

namespace Carno\NSQ\Types;

use Carno\NSQ\Connector\Nsqd;
use Throwable;

class Consuming
{
    /**
     * @var string
     */
    private $topic = null;

    /**
     * @var string
     */
    private $channel = null;

    /**
     * @var callable
     */
    private $program = null;

    /**
     * @var int
     */
    private $concurrency = null;

    /**
     * Consuming constructor.
     * @param callable $program
     * @param int $concurrency
     */
    public function __construct(callable $program, int $concurrency = 1)
    {
        $this->program = $program;
        $this->concurrency = $concurrency;
    }

    /**
     * @param Nsqd $nsqd
     * @param Message $message
     */
    public function invoking(Nsqd $nsqd, Message $message) : void
    {
        try {
            call_user_func($this->program, $message->link($nsqd));
        } catch (Throwable $e) {
            logger('nsq')->notice(
                'Consuming invoker failure',
                ['error' => sprintf('%s::%s', get_class($e), $e->getMessage())]
            );
        }
    }

    /**
     * @return int
     */
    public function concurrency() : int
    {
        return $this->concurrency;
    }

    /**
     * @param string $topic
     * @param string $channel
     * @return static
     */
    public function assigned(string $topic, string $channel) : self
    {
        $this->topic = $topic;
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return string
     */
    public function topic() : string
    {
        return $this->topic;
    }

    /**
     * @return string
     */
    public function channel() : string
    {
        return $this->channel;
    }
}
