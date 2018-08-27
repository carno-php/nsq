<?php
/**
 * Cluster but NO consumer
 * User: moyo
 * Date: 2018/7/9
 * Time: 11:16 AM
 */

namespace Carno\NSQ\Clustered;

use Carno\NSQ\Types\Consuming;

trait NOConsumer
{
    /**
     * @var bool
     */
    protected $withoutConsumer = true;

    /**
     * @return Consuming
     */
    protected function consuming() : Consuming
    {
        return new Consuming(function () {
            // do nothing
        });
    }
}
