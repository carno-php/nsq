<?php
namespace NSQ_P;

require __DIR__ . '/../vendor/autoload.php';

use function Carno\Coroutine\go;
use Carno\NSQ\Components\Lookupd;
use Carno\NSQ\Producer;
use Carno\NSQ\Types\Message;
use Carno\Timer\Timer;
use Throwable;

go(static function () {
    $lookupd = new Lookupd(...explode(':', env('LOOKUPD', '127.0.0.1:4161')));

    $p = new Producer(env('QUEUED', 0));

    $p->setLookupd($lookupd);
    $p->setTopic(env('TOPIC', 'carno-nsq-tests'));

    $monitor = Timer::loop(3000, function () {
        echo 'memory usage is ', round(memory_get_usage() / pow(1024, 2), 2), 'MB', PHP_EOL;
    });

    $initialized = $p->startup();

    go(function () use ($p) {
        $idx = 0;
        $sum = rand(env('SENT_MIN', 100), env('SENT_MAX', 500));
        $progress = Timer::loop(500, function () use (&$idx, $sum) {
            echo 'current sent ', $idx, '/', $sum, ' messages', PHP_EOL;
        });
        try {
            for ($idx = 0; $idx < $sum; $idx ++) {
                yield $p->publish(new Message($idx));
            }
        } catch (Throwable $e) {
            echo get_class($e), '::', $e->getMessage(), PHP_EOL;
            echo $e->getTraceAsString(), PHP_EOL;
        }
        echo 'finally sent ', $idx, '/', $sum, ' messages', PHP_EOL;
        Timer::clear($progress);
        $p->shutdown();
    });

    yield $initialized;

    Timer::clear($monitor);

    echo 'producer stopped', PHP_EOL;
});
