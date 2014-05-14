<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Tests\Adapter;


use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Adapter\InotifyAdapter;

class InotifyAdapterTest extends AdapterTest
{
    public function setUp()
    {
        if(!function_exists('inotify_init')){
            $this->markTestIncomplete('PHP inotify extension not installed');
        }
        parent::setUp();
    }
    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return new InotifyAdapter();
    }

    protected function getMinimumInterval()
    {
        return 0;
    }
}
 