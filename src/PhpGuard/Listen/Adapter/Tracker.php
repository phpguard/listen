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
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\DirectoryResource;
use PhpGuard\Listen\Resource\FileResource;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Util\PathUtil;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

    private $changeSet = array();

    private $map;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    private $fileOnly = true;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

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
    public function has($trackID)
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
    public function get($trackID)
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
    public function add(TrackedObject $tracked)
    {
        $id = $tracked->getID();
        if(isset($this->map[$id])){
            return;
        }
        $this->map[$id] = $tracked;
        $this->adapter->watch($tracked);
    }

    public function remove(TrackedObject $tracked)
    {
        unset($this->map[$tracked->getID()]);
        $this->adapter->unwatch($tracked);
    }

    public function getTracks()
    {
        return $this->map;
    }

    /**
     * @param $path
     * @throws \PhpGuard\Listen\Exception\InvalidArgumentException
     * @internal param \Symfony\Component\Finder\SplFileInfo $spl
     */
    public function checkPath($path)
    {
        $id = PathUtil::createPathID($path);

        if(!$this->has($id)){
            if(!is_readable($path)) return;
            if(!$path instanceof SplFileInfo){
                $absPath = realpath($path);
                foreach($this->listener->getPaths() as $baseDir)
                {
                    $baseDir = realpath($baseDir);
                    $baseDirLen = strlen($baseDir);

                    if($baseDir === substr($absPath,0,$baseDirLen)){
                        $path = PathUtil::createSplFileInfo($baseDir,$absPath);
                        break;
                    }
                }
            }

            if(!$path instanceof SplFileInfo){
                throw new InvalidArgumentException(sprintf(
                    'Path "%s" can not registered',
                    $path
                ));
            }

            if(!$this->listener->hasPath($path)){
                return;
            }

            // path is new
            if($path->isFile()){
                $resource = new FileResource($path);
            }else{
                $resource = new DirectoryResource($path);
            }

            $tracked = $this->createTrackedObject($resource);
            $this->add($tracked);
            $this->addChangeSet($tracked,FilesystemEvent::CREATE);
            return;
        }

        $tracked = $this->get($id);
        $origin = $tracked->getResource();

        if(
            $tracked->getChecksum()
            !== $origin->getChecksum()
            && $origin->isExists())
        {
            // path is modified
            $this->addChangeSet($tracked,FilesystemEvent::MODIFY);
            $tracked->setChecksum($origin->getChecksum());
            unset($this->unchecked[$id]);

            return;
        }

        if(!$origin->isExists()){
            // path is deleted
            $this->addChangeSet($tracked,FilesystemEvent::DELETE);
            $this->remove($tracked);
        }

    }

    /**
     * @return Finder
     */
    public function createFinder()
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

    public function clearChangeSet()
    {
        $this->changeSet = array();
    }

    /**
     * @return mixed
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    public function fileOnly($value)
    {
        $this->fileOnly = $value;
    }

    /**
     * Add a new event to changeset
     *
     *@param TrackedObject $tracked
     * @param int             $eventMask
     */
    public function addChangeSet($tracked,$eventMask)
    {
        if($tracked instanceof TrackedObject){
            $path = $tracked->getResource();
            if($this->fileOnly && !$tracked->getResource() instanceof FileResource){
                return;
            }
        }else{
            $path = $tracked;
        }
        $event = new FilesystemEvent($path,$eventMask);
        $this->changeSet[] = $event;
    }

    /**
     * Scan path and add it to listener
     * @param string $path
     */
    private function scanDir($path)
    {
        if(!is_dir($path)) return;

        /* @var \Symfony\Component\Finder\SplFileInfo $spl */
        $finder = $this->createFinder();

        $rootSPL = new DirectoryResource($path);
        $rootResource = new DirectoryResource($rootSPL);
        $rootResource = $this->createTrackedObject($rootResource);

        $this->add($rootResource);

        foreach($finder->in($path) as $spl)
        {
            if($spl->isFile()){
                $resource = new FileResource($spl);
            }else{
                $resource = new DirectoryResource($spl);
            }
            $trackedResource = $this->createTrackedObject($resource);
            $this->add($trackedResource);
        }
    }

    /**
     * Create new TrackedObject
     *
     * @param   ResourceInterface   $resource
     * @return  TrackedObject
     */
    public function createTrackedObject(ResourceInterface $resource)
    {
        $tracked = new TrackedObject();
        $tracked->setResource($resource);
        $tracked->setChecksum($resource->getChecksum());

        if(is_null($tracked->getID())){
            $tracked->setID(PathUtil::createPathID($resource->getResource()));
        }
        return $tracked;
    }
}
