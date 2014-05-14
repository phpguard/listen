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

use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Util\LogLevel;
use Psr\Log\LoggerAwareInterface;

interface AdapterInterface extends LoggerAwareInterface
{
    /**
     * Initialize new listener
     *
     * @param Listener $listener
     *
     * @return mixed
     */
    public function initialize(Listener $listener);

    /**
     * Evaluate Filesystem changes
     *
     * @return mixed
     */
    public function evaluate();

    /**
     * Get latest changeset from adapter.
     * @return array An array of FileystemEvent object
     */
    public function getChangeSet();

    public function watch(TrackedObject $tracked);

    public function unwatch(TrackedObject $tracked);

    public function log($message,array $context=array(),$level = LogLevel::DEBUG);
}