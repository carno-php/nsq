<?php
/**
 * Topic+Channel assign
 * User: moyo
 * Date: 15/11/2017
 * Time: 4:05 PM
 */

namespace Carno\NSQ\Chips;

trait TCAssigned
{
    /**
     * @var string
     */
    private $topic = 'default';

    /**
     * @var string
     */
    private $channel = 'default';

    /**
     * @param string $topic
     * @return static
     */
    public function setTopic(string $topic) : self
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @return string
     */
    public function getTopic() : string
    {
        return $this->topic;
    }

    /**
     * @param string $chanel
     * @return static
     */
    public function setChannel(string $chanel) : self
    {
        $this->channel = $chanel;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannel() : string
    {
        return $this->channel;
    }
}
