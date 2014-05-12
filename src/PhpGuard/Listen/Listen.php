<?php

namespace PhpGuard\Listen;

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Adapter\Pooling\PoolingAdapter;
use PhpGuard\Listen\Event\FilesystemEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Listen
 *
 */
class Listen
{
    private $adapter;

    private $dispatcher;

    private $latency;

    private $listeners;

    public function __construct(EventDispatcherInterface $dispatcher=null,AdapterInterface $adapter=null)
    {
        if(is_null($dispatcher)){
            $this->dispatcher = new EventDispatcher();
        }
        if(is_null($adapter)){
            $adapter = new PoolingAdapter();
        }

        $this->adapter = $adapter;
    }

    /**
     * Set directory to initialize
     *
     * @param       $paths
     * @internal    param \PhpGuard\Listen\File $paths or Directory to initialize
     * @internal    param array $options An options for watcher
     * @internal    param int $eventMask
     * @return      Listener
     */
    public function to($paths)
    {
        $adapter = $this->adapter;

        $listener = new Listener($paths);
        $this->listeners[] = $listener;
        return $listener;
    }
}
