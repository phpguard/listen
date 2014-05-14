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

use PhpGuard\Listen\Exception\InvalidArgumentException;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\DirectoryResource;
use PhpGuard\Listen\Resource\FileResource;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Util\PathUtil;
use Symfony\Component\Finder\Finder;

/**
 * Class Tracker
 *
 */
class Tracker
{
    /**
     * @var Listener
     */
    private $listener;

    private $changeSet;

    private $map;

    /**
     * @param Listener $listener
     */
    public function initialize(Listener $listener)
    {
        $this->listener = $listener;
        foreach($listener->getPaths() as $path)
        {
            $this->scanDir($path);
        }
    }

    /**
     * Check if TrackedObject with $resourceID
     * is registered
     *
     * @param $trackID
     * @return bool
     */
    public function hasTrack($trackID)
    {
        $absPath = realpath((string)$trackID);
        if(is_dir($absPath) || is_file($absPath)){
            $trackID = PathUtil::createPathID($absPath);
        }
        return isset($this->map[$trackID]) ? true:false;
    }

    /**
     * Get track based on given $trackID
     *
     * If the given $trackID is a file/directory name
     * it will be converted automatically into track id
     * @param   string $trackID
     * @throws \PhpGuard\Listen\Exception\InvalidArgumentException When trackID is not registered
     * @return  TrackedObject
     */
    public function getTrack($trackID)
    {
        $id = $trackID;
        if(is_dir($trackID) || is_file($trackID)){
            $id = PathUtil::createPathID($trackID);
        }
        if(!isset($this->map[$id])){
            throw new InvalidArgumentException(sprintf(
                'Track ID: "%s" is not registered',
                $trackID
            ));
        }
        return $this->map[$id];
    }

    /**
     * Add a new TrackedObject into map
     * @param TrackedObject $tracked
     */
    public function addTrack(TrackedObject $tracked)
    {
        $id = $tracked->getID();
        if(isset($this->map[$id])){
            return;
        }
        $this->map[$id] = $tracked;
    }

    /**
     * @param SplFileInfo $spl
     *
     * @author Anthonius Munthi <me@itstoni.com>
     */
    public function checkPath(SplFileInfo $spl)
    {
        $absPath = $spl->getRealPath();

        if($spl->isFile()){
            $id = md5('f'.$absPath);
        }
        else{
            $id = md5('d'.$absPath);
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
     * @return mixed
     */
    public function getChangeSet()
    {
        return $this->changeSet;
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
    private function scanDir($path)
    {
        if(!is_dir($path)) return;

        /* @var \Symfony\Component\Finder\SplFileInfo $spl */
        $finder = $this->createFinder();

        $rootSPL = new DirectoryResource($path);
        $rootResource = new DirectoryResource($rootSPL);
        $rootResource = $this->createTrackedObject($rootResource);

        $this->addTrack($rootResource);

        foreach($finder->in($path) as $spl)
        {
            if($spl->isFile()){
                $resource = new FileResource($spl);
            }else{
                $resource = new DirectoryResource($spl);
            }
            $trackedResource = $this->createTrackedObject($resource);
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
        $tracked->setID($resource->getID());
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

    /**
     * @return Finder
     */
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