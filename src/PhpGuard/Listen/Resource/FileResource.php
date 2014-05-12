<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Resource;

/**
 * Class FileResource
 *
 */
class FileResource implements ResourceInterface
{
    private $resource;

    private $trackingID;

    private $checksum;

    public function __construct($resource)
    {
        $this->resource = file_exists($resource) ? realpath($resource):$resource;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->resource);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->resource = unserialize($serialized);
    }

    /**
     * Returns unique resource ID.
     *
     * @return string
     */
    public function getID()
    {
        return md5('f'.(string)$this->resource);
    }

    /**
     * Returns resource mtime.
     *
     * @return integer
     */
    public function getModificationTime()
    {
        if(!$this->isExists()){
            return -1;
        }
        clearstatcache(true,$this->resource);
        return filemtime($this->resource);
    }

    /**
     * Returns the resource tied to this Resource.
     *
     * @return mixed The resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns true if the resource has not been updated since the given timestamp.
     *
     * @param integer $timestamp The last time the resource was loaded
     *
     * @return Boolean true if the resource has not been updated, false otherwise
     */
    public function isFresh($timestamp)
    {
        if(!$this->isExists()){
            return false;
        }

        return $this->getModificationTime() <= $timestamp;
    }

    /**
     * Returns true if the resource exists in the filesystem.
     *
     * @return Boolean
     */
    public function isExists()
    {
        return is_file($this->resource);
    }

    /**
     * Set tracking id for a resource
     * @param   mixed $id
     * @return  $this
     */
    public function setTrackingID($id)
    {
        $this->trackingID = $id;
    }

    /**
     * Get tracking id for a resource
     * @return mixed Tracking ID for the resource
     */
    public function getTrackingID()
    {
        return $this->trackingID;
    }

    public function getChecksum()
    {
        if(!$this->isExists()){
            return null;
        }

        return md5(md5_file(realpath($this->resource)).$this->getModificationTime());
    }

    /**
     * Returns a string representation of the Resource.
     *
     * @return string A string representation of the Resource
     */
    public function __toString()
    {
        return $this->resource;
    }
}
