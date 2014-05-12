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
    private $listenerInitialized = false;

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
     * Create a new listener
     *
     * @param       $paths
     * @return      Listener
     */
    public function to($paths)
    {
        // listeners maybe not fully defined yet
        // so we should not added listener to adapter
        $listener = new Listener();
        $listener->to($paths);
        $this->listeners[] = $listener;
        return $listener;
    }

    public function start()
    {
        // finally listener is fully defined
        // ask adapter to initialize this listener
        $this->initializeListeners();

        return $this;
    }

    /**
     * Initialize all registered listener
     */
    private function initializeListeners()
    {
        if($this->listenerInitialized){
            return;
        }

        foreach($this->listeners as $listener){
            $this->adapter->initialize($listener);
        }

        $this->listenerInitialized = true;
    }

}
