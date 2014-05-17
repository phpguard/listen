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

    public function __construct($resource)
    {
        $this->resource = $resource;
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
     * Returns the resource tied to this Resource.
     *
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns true if the resource exists in the filesystem.
     *
     * @return Boolean
     */
    public function isExists()
    {
        clearstatcache(true,(string)$this->resource);
        return is_file((string)$this->resource);
    }

    /**
     * @return null|string
     */
    public function getChecksum()
    {
        if(!$this->isExists()){
            return null;
        }

        clearstatcache(true,$this->getResource());

        return md5(md5_file(realpath($this->resource)).filemtime($this->getResource()));
    }

    /**
     * Returns a string representation of the Resource.
     *
     * @return string A string representation of the Resource
     */
    public function __toString()
    {
        return realpath($this->resource);
    }
}
