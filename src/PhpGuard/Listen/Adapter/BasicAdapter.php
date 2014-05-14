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

use PhpGuard\Listen\Resource\ResourceManager;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class BasicAdapter
 *
 */
class BasicAdapter extends BaseAdapter
{
    /**
     * @var Listener
     */
    private $listener;

    /**
     * @var Tracker
     */
    private $tracker;

    private $topDirs = array();

    public function __construct()
    {
        $this->tracker = new Tracker($this);
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
        $this->topDirs = array_merge($this->topDirs,$listener->getPaths());
        $this->tracker->initialize($listener);
    }

    /**
     * @return array
     */
    public function evaluate()
    {
        $tracker = $this->tracker;
        $unchecked = $tracker->getTracks();
        $tracker->clearChangeSet();
        /* @var TrackedObject $resource */
        /* @var SplFileInfo $spl */
        foreach($this->topDirs as $path){
            if(!is_dir($path)){
                continue;
            }
            $finder = $tracker->createFinder();
            foreach($finder->in($path) as $spl)
            {
                $tracker->checkPath($spl->getRealPath());
                $id = $tracker->get($spl)->getID();
                unset($unchecked[$id]);
            }
        }

        // cleanup any undetected changes
        $this->doCleanup($unchecked);
    }

    public function getChangeSet()
    {
        return $this->tracker->getChangeSet();
    }

    /**
     * Track any undetected changes
     * such as recursive directory delete
     */
    private function doCleanup($unchecked)
    {
        $tracker = $this->tracker;
        /* @var TrackedObject $tracked */
        foreach($unchecked as $id=>$tracked){
            $origin = $tracked->getResource();
            if(!$origin->isExists()){
                $tracker->addChangeSet($tracked,FilesystemEvent::DELETE);
                $tracker->remove($tracked);
            }
            unset($unchecked[$id]);
        }
    }
}