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
use PhpGuard\Listen\Adapter\BasicAdapter;
use PhpGuard\Listen\Adapter\Inotify\InotifyAdapter;
use PhpGuard\Listen\Event\FilesystemEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Listen
 *
 */
class Listen
{
    /**
     * Create a new listener
     *
     * @param       $paths
     * @return      Listener
     */
    static public function to($paths)
    {
        // listeners maybe not fully defined yet
        // so we should not added listener to adapter
        $listener = new Listener();

        if(function_exists('inotify_init')){
            $adapter = new BasicAdapter();
        }else{
            $adapter = new BasicAdapter();
        }

        $listener
            ->setAdapter($adapter)
            ->to($paths)
        ;

        return $listener;
    }
}
