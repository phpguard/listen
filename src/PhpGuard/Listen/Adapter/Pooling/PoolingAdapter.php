<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Adapter\Pooling;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\ResourceManager;
use PhpGuard\Listen\Listener;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class PoolingAdapter
 *
 */
class PoolingAdapter implements AdapterInterface,LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $resourceManager;
    
    private $listeners = array();

    public function __construct()
    {
        $this->resourceManager = new ResourceManager($this);
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function initialize(Listener $listener)
    {
        $this->resourceManager->scan($listener);
        $this->listeners[] = $listener;
    }

    public function getEvents()
    {
        return array();
    }

    public function log($message,array $context = array(),$level=LogLevel::DEBUG)
    {
        if(!isset($this->logger)){
            return;
        }

        $this->logger->log($level,$message,$context);
    }

    public function watch(ResourceInterface $resource)
    {
        $resource->setTrackingID($resource->getID());
    }

    public function unwatch(ResourceInterface $resourceInterface)
    {

    }

    public function start()
    {

    }


}