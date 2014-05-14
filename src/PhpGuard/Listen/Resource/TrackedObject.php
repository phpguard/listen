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
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\ResourceInterface;

/**
 * Class TrackedObject
 *
 */
class TrackedObject
{
    /**
     * @var string
     */
    private $id;

    /**
     * Original resource to track
     * @var ResourceInterface
     */
    private $resource;

    private $checksum;

    /**
     * @param string $id
     *
     * @return TrackedObject
     */
    public function setID($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param \PhpGuard\Listen\Resource\ResourceInterface $originalResource
     *
     * @return TrackedObject
     */
    public function setResource(ResourceInterface $originalResource)
    {
        $this->resource = $originalResource;
        return $this;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $checksum
     *
     * @return TrackedObject
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChecksum()
    {
        return $this->checksum;
    }
}
