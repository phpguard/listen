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
     * @return boolean True if resource exists
     */
    public function isExists()
    {
        return is_dir($this->resource);
    }

    /**
     * @return string Realpath of the resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getChecksum()
    {
        return md5(spl_object_hash($this));
    }

    /**
     * @return string Realpath of the resource
     */
    public function __toString()
    {
        return (string)$this->resource;
    }
}