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
     * @param       $path File or Directory to initialize
     * @param       array $options An options for watcher
     * @param       int $eventMask
     * @internal    param string $directory
     * @return      Listener
     */
    public function to($path,$options,$eventMask=FilesystemEvent::ALL)
    {
        $adapter = $this->adapter;

        $watcher = new Listener();

        //$watcher->setOptions($options);
        $watcher->setEventMask($eventMask);
        $watcher->setPath($path);

        $adapter->initialize($watcher);

        return $watcher;
    }
}
