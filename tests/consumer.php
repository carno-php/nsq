<?php
namespace NSQ_C;

require __DIR__ . '/../vendor/autoload.php';

use function Carno\Coroutine\go;
use Carno\NSQ\Components\Lookupd;
use Carno\NSQ\Consumer;
use Carno\NSQ\Types\Consuming;
use Carno\NSQ\Types\Message;
use Carno\Timer\Timer;

go(static function () {
    $lookupd = new Lookupd(...explode(':', env('LOOKUPD', '127.0.0.1:4161')));

    $got = 0;

    $c = new Consumer(new Consuming(function (Message $message) use (&$got) {
        if (env('VERBOSE', 0) > 0) {
            echo '#', $message->id(), ' > recv message is ', $message->payload(), PHP_EOL;
        }
        $message->done();
        $got ++;
    }, env('CONCURRENCY', 1)));

    $c->setLookupd($lookupd);
    $c->setTopic(env('TOPIC', 'carno-nsq-tests'));
    $c->setChannel(env('CHANNEL', 'sub'));

    $progress = Timer::loop(500, function () use (&$got) {
        echo 'current recv ', $got, ' messages', PHP_EOL;
    });

    $last = 0;

    $monitor = Timer::loop(3000, function () use ($c, &$last, &$got) {
        echo 'memory usage is ', round(memory_get_usage() / pow(1024, 2), 2), 'MB', PHP_EOL;
        if ($last !== $got) {
            $last = $got;
        } else {
            echo 'long time no messages received', PHP_EOL;
            $c->shutdown();
        }
    });

    yield $c->startup();

    Timer::clear($progress);
    Timer::clear($monitor);

    echo 'consumer stopped', PHP_EOL;
});
