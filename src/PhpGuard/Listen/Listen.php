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
use PhpGuard\Listen\Adapter\InotifyAdapter;

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

        $listener
            ->to($paths)
        ;

        return $listener;
    }

    /**
     * @return  AdapterInterface
     * @author Anthonius Munthi <me@itstoni.com>
     */
    static public function getDefaultAdapter()
    {
        if(function_exists('inotify_init')){
            $adapter = new InotifyAdapter();
        }else{
            $adapter = new BasicAdapter();
        }
        return $adapter;
    }
}
