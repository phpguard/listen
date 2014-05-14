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
use PhpGuard\Listen\Listener;

interface AdapterInterface
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
     * @return array Listeners for this adapter
     */
    public function getListener();

    /**
     * Get latest changeset from adapter.
     * @return array An array of FileystemEvent object
     */
    public function getChangeSet();
}