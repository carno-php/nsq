<?php
/**
 * NSQ instance linker
 * User: moyo
 * Date: 26/02/2018
 * Time: 3:13 PM
 */

namespace Carno\NSQ\Chips;

use Carno\Net\Endpoint;
use Carno\NSQ\Connector\Linker;
use Carno\NSQ\Types\Consuming;
use Closure;
use Throwable;

trait INSLinker
{
    /**
     * @var Linker[]
     */
    private $linked = [];

    /**
     * @param Endpoint $endpoint
     * @param Consuming $consuming
     * @return Linker
     */
    protected function linking(Endpoint $endpoint, Consuming $consuming = null) : Linker
    {
        return
            $this->linked[(string)$endpoint->address()] ??
            $this->linked[(string)$endpoint->address()] = new Linker($endpoint, $consuming);
    }

    /**
     * @param Endpoint $endpoint
     * @return Linker
     */
    protected function linker(Endpoint $endpoint) : Linker
    {
        return $this->linked[(string)$endpoint->address()];
    }

    /**
     * @param Closure $operator
     */
    protected function linkers(Closure $operator) : void
    {
        foreach ($this->linked as $linker) {
            try {
                $operator($linker);
            } catch (Throwable $e) {
                logger('nsq')->notice(
                    'Linker operating failed',
                    ['error' => sprintf('%s::%s', get_class($e), $e->getMessage())]
                );
            }
        }
    }
}
