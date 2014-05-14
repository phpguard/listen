<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Adapter;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Adapter\Basic\Tracker;
use PhpGuard\Listen\Resource\ResourceManager;
use PhpGuard\Listen\Resource\DirectoryResource;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\FileResource;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

    /**
     * @var Listener
     */
    private $listener;

    /**
     * @var Tracker
     */
    private $tracker;

    private $map = array();

    private $changeSet = array();

    private $unchecked = array();

    private $topDirs = array();

    public function getListener()
    {
        return $this->listener;
    }

    /**
     * Initialize a listener
     *
     * @param   Listener $listener
     * @return  void
     */
    public function initialize(Listener $listener)
    {
        $this->listener = $listener;
        foreach($listener->getPaths() as $path)
        {
            $this->scanDir($path,$listener);
        }
        $this->topDirs = array_merge($this->topDirs,$listener->getPaths());
    }

    /**
     * @return array
     */
    public function evaluate()
    {
        $this->changeSet = array();
        $this->unchecked = $this->map;

        /* @var TrackedObject $resource */
        /* @var SplFileInfo $spl */
        foreach($this->topDirs as $path){
            if(!is_dir($path)){
                continue;
            }
            $finder = $this->createFinder();

            foreach($finder->in($path) as $spl)
            {
                $spl = new SplFileInfo($spl->getRealPath(),$spl->getRelativePath(),$spl->getRelativePathname());
                $this->doCheckPath($spl);
            }
        }

        // cleanup any undetected changes
        $this->doCleanup();
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

    public function log($message,array $context = array(),$level=LogLevel::DEBUG)
    {
        // @codeCoverageIgnoreStart

        if(!isset($this->logger)){
            return;
        }
        // @codeCoverageIgnoreEnd

        $this->logger->log($level,$message,$context);
    }

    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * Check if TrackedObject with $resourceID
     * has been registered
     *
     * @param $resourceID
     * @return bool
     */
    private function hasTrack($resourceID)
    {
        return isset($this->map[$resourceID]) ? true:false;
    }

    /**
     * @param   string  $resourceID
     * @return  TrackedObject
     */
    private function getTrack($resourceID)
    {
        return $this->map[$resourceID];
    }

    /**
     * Add a new TrackedObject into map
     * @param TrackedObject $resource
     */
    private function addTrack(TrackedObject $resource)
    {
        $id = $resource->getID();
        if(isset($this->map[$id])){
            return;
        }
        $this->map[$id] = $resource;
    }

    /**
     * Track any undetected changes
     * such as recursive directory delete
     */
    private function doCleanup()
    {
        /* @var TrackedObject $tracked */
        foreach($this->unchecked as $id=>$tracked){
            $origin = $tracked->getResource();
            if(!$origin->isExists()){
                //print_r($origin);
                $this->addChangeSet($tracked,FilesystemEvent::DELETE);
                unset($this->map[$id]);
                unset($this->unchecked[$id]);
            }
        }
    }

    /**
     * @param SplFileInfo $spl
     *
     * @author Anthonius Munthi <me@itstoni.com>
     */
    private function doCheckPath(SplFileInfo $spl)
    {
        $abspath = $spl->getRealPath();

        if($spl->isFile()){
            $id = md5('f'.$abspath);
        }
        else{
            $id = md5('d'.$abspath);
        }

        if(!$this->hasTrack($id)){

            if($spl->isFile()){
                $resource = new FileResource($spl);
            }else{
                $resource = new DirectoryResource($spl);
            }
            $tracked = $this->createTrackedObject($resource);
            $this->addTrack($tracked);
            $this->addChangeSet($tracked,FilesystemEvent::CREATE);
            return;
        }

        $tracked = $this->getTrack($id);
        $origin = $tracked->getResource();
        if(
            $tracked->getChecksum()
            !== $origin->getChecksum()
            && $origin->isExists())
        {
            // file is modified add to changeset
            $this->addChangeSet($tracked,FilesystemEvent::MODIFY);
            $tracked->setChecksum($origin->getChecksum());
            unset($this->unchecked[$id]);

            return;
        }

        if(!$origin->isExists()){
            $this->addChangeSet($tracked,FilesystemEvent::DELETE);
            unset($this->map[$id]);
            unset($this->unchecked[$id]);
        }

    }

    /**
     * Add a new event to changeset
     *
     *@param TrackedObject $tracked
     * @param int             $eventMask
     */
    private function addChangeSet(TrackedObject $tracked,$eventMask)
    {
        $origin = $tracked->getResource();

        if(!$origin instanceof FileResource){
            return;
        }

        $event = new FilesystemEvent($origin->getResource(),$eventMask);

        $this->changeSet[] = $event;
    }

    /**
     * Scan listener paths,
     * and add its resources to map
     *
     * @param string $path
     * @param Listener  $listener
     */
    private function scanDir($path,Listener $listener)
    {
        if(!is_dir($path)) return;

        /* @var \Symfony\Component\Finder\SplFileInfo $spl */
        $finder = $this->createFinder();

        $rootSPL = new DirectoryResource($path);
        $rootResource = new DirectoryResource($rootSPL);
        $rootResource = $this->createTrackedObject($rootResource,$listener);

        $this->addTrack($rootResource);

        foreach($finder->in($path) as $spl)
        {
            if($spl->isFile()){
                $resource = new FileResource($spl);
            }else{
                $resource = new DirectoryResource($spl);
            }
            $trackedResource = $this->createTrackedObject($resource,$listener);
            $this->addTrack($trackedResource);
        }
    }

    /**
     * Create new TrackedObject
     *
     * @param   ResourceInterface   $resource
     * @return  TrackedObject
     */
    private function createTrackedObject(ResourceInterface $resource)
    {
        $id = $resource->getID();

        if($this->hasTrack($id)){
            // resource already exists
            // return existing one
            return $this->getTrack($id);
        }

        $tracked = new TrackedObject();
        $tracked->setId($resource->getID());
        $tracked->setResource($resource);
        $tracked->setChecksum($resource->getChecksum());

        $absPath = realpath($resource);
        $dirName = dirname($absPath);
        $parentID = md5('d'.$dirName);
        if($this->hasTrack($parentID)){
            $this->getTrack($parentID)->getResource()->addChild($resource);
        }

        return $tracked;
    }

    private function createFinder()
    {
        $finder = Finder::create();
        $listener = $this->listener;

        $finder->filter(function($spl) use($listener){
            return $listener->hasPath($spl);
        });

        foreach($listener->getIgnores() as $ignored){
            $finder->notPath($ignored);
        }

        return $finder;
    }
}