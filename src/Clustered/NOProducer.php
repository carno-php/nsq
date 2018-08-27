<?php
/**
 * Cluster but NO producer
 * User: moyo
 * Date: 2018/7/9
 * Time: 11:16 AM
 */

namespace Carno\NSQ\Clustered;

trait NOProducer
{
    /**
     * @var bool
     */
    protected $withoutProducer = true;

    /**
     * @return int
     */
    protected function producing() : int
    {
        return 0;
    }
}
