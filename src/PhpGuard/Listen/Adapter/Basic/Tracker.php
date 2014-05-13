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

use PhpGuard\Listen\Event\ChangeSetEvent;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\DirectoryResource;
use PhpGuard\Listen\Resource\FileResource;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\SplFileInfo;
use PhpGuard\Listen\Resource\TrackedResource;
use Symfony\Component\Finder\Finder;

/**
 * Class Tracker
 *
 */
class Tracker
{
    private $map = array();

    private $changeSet = array();

    private $unchecked = array();

    private $listeners = array();

    private $topDirs = array();

    /**
     * Add and scan new listener
     *
     * @param Listener $listener
     */
    public function add(Listener $listener)
    {
        foreach($listener->getPaths() as $path)
        {
            $this->scanDir($path,$listener);
        }
        $this->listeners[] = $listener;
        $this->topDirs = array_merge($this->topDirs,$listener->getPaths());
    }

    /**
     * Check if TrackedResource with $resourceID
     * has been registered
     *
     * @param $resourceID
     * @return bool
     */
    public function hasResource($resourceID)
    {
        return isset($this->map[$resourceID]) ? true:false;
    }

    /**
     * @param   string  $resourceID
     * @return  TrackedResource
     */
    public function getResource($resourceID)
    {
        return $this->map[$resourceID];
    }

    /**
     * Add a new TrackedResource into map
     * @param TrackedResource $resource
     */
    public function addResource(TrackedResource $resource)
    {
        $id = $resource->getID();
        if(isset($this->map[$id])){
            return;
        }
        $this->map[$id] = $resource;
    }

    /**
     * Refresh Filsystem state
     * and detect any changes
     */
    public function refresh()
    {
        $this->changeSet = array();
        $this->unchecked = $this->map;

        /* @var TrackedResource $resource */
        /* @var SplFileInfo $spl */
        foreach($this->topDirs as $path){
            if(!is_dir($path)){
                continue;
            }
            $finder = Finder::create()
                ->notName('vendor')
            ;
            foreach($finder->in($path) as $spl)
            {
                $spl = new SplFileInfo($spl->getRealPath(),$spl->getRelativePath(),$spl->getRelativePathname());
                $this->doCheckPath($spl);
            }
        }

        // cleanup any undetected changes
        $this->doCleanup();

        /* @var Listener $listener*/
        /* @var FilesystemEvent $event */
        foreach($this->listeners as $listener)
        {
            $filtered = array();
            $listener->setChangeSet(array());
            foreach($this->changeSet as $event){
                if($listener->hasPath($event->getResource())){
                    $filtered[] = $event;
                }
            }
            $listener->setChangeSet($filtered);
        }
    }

    /**
     * Track any undetected changes
     * such as recursive directory delete
     */
    private function doCleanup()
    {
        /* @var TrackedResource $tracked */
        foreach($this->unchecked as $id=>$tracked){
            $origin = $tracked->getOriginalResource();
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

        if(!$this->hasResource($id)){
            if($spl->isFile()){
                $resource = new FileResource($spl);
            }else{
                $resource = new DirectoryResource($spl);
            }

            $dirname = dirname($abspath);
            $parentID = md5('d'.$dirname);
            $parent = $this->getResource($parentID);
            $parent->getOriginalResource()->addChild($resource);

            $tracked = $this->createTrackedResource($resource);
            $this->addResource($tracked);
            $this->addChangeSet($tracked,FilesystemEvent::CREATE);
            return;
        }

        $tracked = $this->getResource($id);
        $origin = $tracked->getOriginalResource();
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
     * @param TrackedResource $tracked
     * @param int             $eventMask
     */
    private function addChangeSet(TrackedResource $tracked,$eventMask)
    {
        $origin = $tracked->getOriginalResource();
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

        /* @var \PhpGuard\Listen\Resource\SplFileInfo $spl */
        $finder = Finder::create()
            ->notPath('vendor')
        ;

        $rootSPL = new DirectoryResource($path);
        $rootResource = new DirectoryResource($rootSPL);
        $rootResource = $this->createTrackedResource($rootResource,$listener);

        $this->addResource($rootResource);

        foreach($finder->in($path) as $spl)
        {
            if($spl->isFile()){
                $resource = new FileResource($spl);
            }else{
                $resource = new DirectoryResource($spl);
            }

            $parent = dirname($spl);
            $parentID = md5('d'.$parent);
            $trackedResource = $this->createTrackedResource($resource,$listener);
            $this->addResource($trackedResource);

            $this->getResource($parentID)
                ->getOriginalResource()
                ->addChild($resource)
            ;
        }
    }

    /**
     * Create new TrackedResource
     *
     * @param   ResourceInterface   $resource
     * @return  TrackedResource
     */
    private function createTrackedResource(ResourceInterface $resource)
    {
        $id = $resource->getID();

        if($this->hasResource($id)){
            // resource already exists
            // return existing one
            return $this->getResource($id);
        }

        $trackedResource = new TrackedResource();
        $trackedResource->setId($resource->getID());
        $trackedResource->setOriginalResource($resource);
        $trackedResource->setChecksum($resource->getChecksum());

        return $trackedResource;
    }
}
