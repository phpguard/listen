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
 * Class TrackedResource
 *
 */
class TrackedResource
{
    /**
     * @var string
     */
    private $id;

    /**
     * Original resource to track
     * @var ResourceInterface
     */
    private $originalResource;

    /**
     * Listeners which listen this resource
     * @var \SplObjectStorage
     */
    private $listeners;

    private $checksum;

    public function __construct()
    {
        $this->listeners = new \SplObjectStorage();
    }

    /**
     * @param string $id
     *
     * @return TrackedResource
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \PhpGuard\Listen\Resource\ResourceInterface $originalResource
     *
     * @return TrackedResource
     */
    public function setOriginalResource(ResourceInterface $originalResource)
    {
        $this->originalResource = $originalResource;
        return $this;
    }

    /**
     * @return ResourceInterface
     */
    public function getOriginalResource()
    {
        return $this->originalResource;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @param mixed $checksum
     *
     * @return TrackedResource
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
