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
 * Class DirectoryResource
 *
 */
class DirectoryResource implements ResourceInterface
{
    private $id;

    private $resource;

    private $pattern;

    private $mtime;

    private $trackingID;

    private $checksum;

    private $childs = array();

    private $parent;

    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->id = md5('d'.(string) $resource);
    }

    /**
     * @return string The Resource ID
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param   $timestamp
     * @return  $this
     */
    public function setModificationTime($timestamp)
    {
        $this->mtime = $timestamp;

        return $this;
    }

    /**
     * @return int Resource Modification Time
     */
    public function getModificationTime()
    {
        if(!$this->isExists()){
            return -1;
        }
        clearstatcache(true,$this->resource);
        $newest = filemtime($this->resource);
        foreach($this->childs as $child){
            clearstatcache(true,$child);
            $newest = max(filemtime($child),$newest);
        }

        return $newest;
    }

    /**
     * @return boolean True if resource exists
     */
    public function isExists()
    {
        return is_dir($this->resource);
    }

    /**
     * @param   int $timestamp A Timestamp to compare
     * @return  boolean True if resource is fresh
     */
    public function isFresh($timestamp)
    {
        if(!$this->isExists()){
            return false;
        }

        return $this->getModificationTime() < $timestamp;
    }

    /**
     * @return string Realpath of the resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string Realpath of the resource
     */
    public function __toString()
    {
        return (string)$this->resource;
    }

    public function serialize()
    {
        return serialize(array($this->resource, $this->pattern));
    }

    public function unserialize($serialized)
    {
        list($this->resource, $this->pattern) = unserialize($serialized);
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
        return md5(spl_object_hash($this).count($this->childs));
    }

    public function addChild(ResourceInterface $resource)
    {
        $this->childs[$resource->getID()] = $resource;
        $resource->setParent($this);
    }

    public function removeChild(ResourceInterface $resource)
    {
        unset($this->childs[$resource->getID()]);
    }

    public function setParent(ResourceInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

}