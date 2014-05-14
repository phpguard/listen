<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Resource;


/**
 * ResourceInterface is the interface that must be implemented by all Resource classes.
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
interface ResourceInterface
{
    /**
     * Returns unique resource ID.
     *
     * @return string
     */
    public function getID();

    /**
     * Returns the resource tied to this Resource.
     *
     * @return mixed The resource
     */
    public function getResource();

    /**
     * Returns true if the resource exists in the filesystem.
     *
     * @return Boolean
     */
    public function isExists();

    /**
     * Get checksum for current resource
     * @return string
     */
    public function getChecksum();

    /**
     * Returns a string representation of the Resource.
     *
     * @return string A string representation of the Resource
     */
    public function __toString();
}