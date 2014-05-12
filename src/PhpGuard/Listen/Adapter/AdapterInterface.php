<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Adapter;


use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Watcher;

interface AdapterInterface
{
    public function initialize(Watcher $watcher);

    public function getEvents();

    public function watch(ResourceInterface $resource);

    public function unwatch(ResourceInterface $resourceInterface);
}