<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Adapter\Basic;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Resource\FileResource;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\ResourceManager;
use PhpGuard\Listen\Listener;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class BasicAdapter
 *
 */
class BasicAdapter implements AdapterInterface,LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $resourceManager;
    
    private $listeners = array();

    private $trackMap = array();

    private $changes = array();

    private $inMonitor = false;

    private $unchecked = array();

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

    /**
     * Initialize a listener
     * @param   Listener $listener
     */
    public function initialize(Listener $listener)
    {
        $this->resourceManager->scan($listener);
        $this->listeners[] = $listener;
    }

    /**
     * @return array
     */
    public function evaluate()
    {
        $this->inMonitor = true;
        $rm = $this->resourceManager;

        /* @var Listener $listener */
        foreach($this->listeners as $listener){
            $this->changes = array();
            $this->unchecked = $this->trackMap;
            $rm->scan($listener);
            // cleanup untracked changes
            $this->cleanup($listener);

            $listener->setChangeSet($this->changes);
        }
        $this->inMonitor = false;
    }

    public function log($message,array $context = array(),$level=LogLevel::DEBUG)
    {
        // @codeCoverageIgnoreStart
        if(!isset($this->logger)){
            return;
        }
        // @codeCoverageIgnoreEnd

        $this->logger->log($level,$message,$context);
    }

    public function watch(ResourceInterface $resource)
    {
        if(false==$this->inMonitor){
            $resource->setTrackingID($resource->getID());
            $this->trackMap[$resource->getID()] = $resource->getChecksum();
            return;
        }

        if(is_null($resource->getTrackingID())){
            $resource->setTrackingID($resource->getID());
            $this->addChangeSet($resource,new FilesystemEvent(
                $resource->getResource(),FilesystemEvent::CREATE
            ));
            $this->trackMap[$resource->getID()] = $resource->getChecksum();
        }else{
            $trackID = $resource->getID();
            $checkSum = $this->trackMap[$trackID];
            if(
                $resource->getChecksum()!==$checkSum
                && $resource->isExists()
                // only file change is tracked
                && $resource instanceof FileResource
            ){
                $this->addChangeSet($resource,new FilesystemEvent(
                    $resource->getResource(),
                    FilesystemEvent::MODIFY
                ));
                $checkSum = $resource->getChecksum();
                $this->trackMap[$trackID] = $checkSum;
                unset($this->unchecked[$trackID]);
            }elseif(!$resource->isExists()){
                $resource->getParent()->removeChild($resource);
                $this->addChangeSet($resource,new FilesystemEvent(
                    $resource->getResource(),
                    FilesystemEvent::DELETE
                ));

                $this->unwatch($resource);
                unset($this->unchecked[$trackID]);
            }
        }
    }

    public function unwatch(ResourceInterface $resource)
    {
        unset($this->trackMap[$resource->getID()]);
        $this->resourceManager->remove($resource);
    }

    private function cleanup()
    {
        // cleanup unchecked files
        $rm = $this->resourceManager;

        foreach($this->unchecked as $id=>$checksum){
            $resource = $rm->getResource($id);
            $path = $resource->getResource();
            $abs = (string)$path;
            clearstatcache(true,$abs);
            if(!$resource->isExists()){
                $this->addChangeSet($resource, new FilesystemEvent(
                    $path,FilesystemEvent::DELETE
                ));
                $this->unwatch($resource);
            }
        }
    }

    /**
     * Add changeset to the resource
     * Only track changeset for file
     * @param ResourceInterface $resource
     * @param FilesystemEvent $event
     */
    private function addChangeSet(ResourceInterface $resource, FilesystemEvent $event)
    {
        if(!$resource instanceof FileResource){
            return;
        }

        $this->changes[] = $event;
    }
}