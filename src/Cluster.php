<?php
/**
 * NSQ via cluster
 * User: moyo
 * Date: 2018/7/6
 * Time: 6:22 PM
 */

namespace Carno\NSQ;

use Carno\Cluster\Classify\Scenes;
use Carno\Cluster\Managed;
use Carno\Cluster\Resources;
use Carno\Net\Endpoint;
use Carno\NSQ\Clustered\Features;
use Carno\NSQ\Types\Consuming;
use Carno\NSQ\Types\Message;
use Carno\Promise\Promise;
use Carno\Promise\Promised;

abstract class Cluster extends Managed
{
    protected const PUB = 'publish';
    protected const SUB = 'subscribe';

    /**
     * @var array
     */
    protected $tags = [self::PUB, self::SUB];

    /**
     * @var string
     */
    protected $type = 'nsq';

    /**
     * @var int
     */
    protected $port = 4161;

    /**
     * @var string
     */
    protected $topic = 'topic';

    /**
     * @var string
     */
    protected $channel = 'channel';

    /**
     * @var Promised
     */
    private $subscribed = null;

    /**
     * Cluster constructor.
     * @param Resources $resources
     */
    public function __construct(Resources $resources)
    {
        $resources->initialize(Scenes::RESOURCE, $this->type, $this->server, $this);
    }

    /**
     * get producer queued cap
     * @return int
     */
    abstract protected function producing() : int;

    /**
     * get consumer processor
     * @return Consuming
     */
    abstract protected function consuming() : Consuming;

    /**
     * @param Endpoint $node
     */
    protected function discovered(Endpoint $node) : void
    {
        in_array(self::SUB, $node->getTags()) && $this->picked($node);
    }

    /**
     * @param Endpoint $endpoint
     * @return Features
     */
    protected function connecting(Endpoint $endpoint) : Features
    {
        return new Features(
            $this->topic,
            $this->channel,
            $endpoint,
            $this->subscribed ?? $this->subscribed = Promise::deferred(),
            (isset($this->withoutProducer) || !in_array(self::PUB, $endpoint->getTags())) ? null : $this->producing(),
            (isset($this->withoutConsumer) || !in_array(self::SUB, $endpoint->getTags())) ? null : $this->consuming()
        );
    }

    /**
     * @param Features $connected
     * @return Promised
     */
    protected function disconnecting($connected) : Promised
    {
        return $connected->shutdown();
    }

    /**
     * @param Message ...$messages
     * @return mixed|bool
     */
    final public function publish(Message ...$messages)
    {
        /**
         * @var Features $features
         */

        $features = yield $this->picking(self::PUB);

        return $features->producer()->publish(...$messages);
    }

    /**
     */
    final public function subscribe()
    {
        $this->subscribed ? $this->subscribed->resolve() : $this->subscribed = Promise::resolved();
    }
}
