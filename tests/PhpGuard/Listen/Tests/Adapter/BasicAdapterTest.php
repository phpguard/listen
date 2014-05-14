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
use PhpGuard\Listen\Adapter\BasicAdapter;
use PhpGuard\Listen\Tests\Adapter\AdapterTest;

class BasicAdapterTest extends AdapterTest
{
    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return new BasicAdapter();
    }

    protected function getMinimumInterval()
    {
        return 1000000;
    }
}
 