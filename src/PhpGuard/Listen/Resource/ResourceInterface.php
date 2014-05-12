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
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Anthonius Munthi <me@itstoni.com>
 */
interface ResourceInterface extends \Serializable
{
    /**
     * Returns unique resource ID.
     *
     * @return string
     */
    public function getID();

    /**
     * Set tracking id for a resource
     * @param   mixed     $id
     * @return  $this
     */
    public function setTrackingID($id);

    /**
     * Get tracking id for a resource
     * @return mixed Tracking ID for the resource
     */
    public function getTrackingID();

    /**
     * Returns resource mtime.
     *
     * @return integer
     */
    public function getModificationTime();

    /**
     * Returns the resource tied to this Resource.
     *
     * @return mixed The resource
     */
    public function getResource();

    /**
     * Returns true if the resource has not been updated since the given timestamp.
     *
     * @param integer $timestamp The last time the resource was loaded
     *
     * @return Boolean true if the resource has not been updated, false otherwise
     */
    public function isFresh($timestamp);

    /**
     * Returns true if the resource exists in the filesystem.
     *
     * @return Boolean
     */
    public function isExists();

    public function getChecksum();

    /**
     * Returns a string representation of the Resource.
     *
     * @return string A string representation of the Resource
     */
    public function __toString();

    /**
     * @param   ResourceInterface $resource
     * @return  mixed
     */
    public function setParent(ResourceInterface $resource);

    /**
     * @return  ResourceInterface
     */
    public function getParent();
}