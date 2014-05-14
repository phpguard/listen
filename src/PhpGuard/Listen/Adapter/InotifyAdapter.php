<?php

namespace PhpGuard\Listen\Adapter;

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\DirectoryResource;
use PhpGuard\Listen\Resource\FileResource;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Util\LogLevel;
use PhpGuard\Listen\Util\PathUtil;
use Psr\Log\LoggerInterface;

/**
 * Class InotifyAdapter
 *
 */
class InotifyAdapter extends BaseAdapter
{
    private $inotify;

    private $inotifyEventMask;

    private $tracker;

    private $inotifyMap = array();

    private $modified = array();

    public function __construct()
    {
        // TODO: add IN_ATTRIB again if possible

        $this->inotifyEventMask = IN_MODIFY | IN_DELETE | IN_CREATE | IN_MOVE | IN_MOVE_SELF;
        $this->inotify = inotify_init();
        stream_set_blocking($this->inotify,0);

        $this->tracker = new Tracker($this);
    }

    /**
     * Initialize new listener
     *
     * @param Listener $listener
     *
     * @return mixed
     */
    public function initialize(Listener $listener)
    {
        $this->tracker->initialize($listener);
    }

    /**
     * Evaluate Filesystem changes
     *
     * @return mixed
     */
    public function evaluate()
    {
        $this->tracker->clearChangeSet();
        $this->modified = array();

        $inEvents = inotify_read($this->inotify);
        $inEvents = is_array($inEvents) ? $inEvents:array();

        foreach($inEvents as $inEvent){

            $this->translateEvent($inEvent);
        }
    }

    /**
     * Get latest changeset from adapter.
     *
     * @return array An array of FileystemEvent object
     */
    public function getChangeSet()
    {
        return $this->tracker->getChangeSet();
    }

    public function watch(TrackedObject $tracked)
    {
        $path = $tracked->getResource();
        //$tracked->setID($id);
        if($tracked->getResource() instanceof FileResource){
            return;
        }
        $id = inotify_add_watch($this->inotify,$path,$this->inotifyEventMask);
        $this->inotifyMap[$id] = $tracked;

        return parent::watch($tracked);
    }

    public function unwatch(TrackedObject $tracked)
    {
        @inotify_rm_watch($this->inotify,$tracked->getID());
        return parent::unwatch($tracked);
    }

    public function __destruct()
    {
        @fclose($this->inotify);
    }

    private function translateEvent($inEvent)
    {
        $tracker = $this->tracker;
        $id = $inEvent['wd'];
        $track = $this->inotifyMap[$id];

        $wdMask = $inEvent['mask'];
        $wdName = $inEvent['name'];
        $resource = $track->getResource();
        $path = $resource.DIRECTORY_SEPARATOR.$wdName;

        if($wdMask & IN_ISDIR){
            if(is_dir($path)){
                // directory not exists should recursive scan directory
                $this->trackNewDir($path);
            }elseif(is_dir($resource)){
                // directory exists let tracker check
                $tracker->checkPath($resource);
            }elseif(!is_dir($resource)){
                // directory is deleted let inotify unwatch
                $this->unwatch($track);
            }
            return;
        }

        $wdMask &= ~IN_ISDIR;
        $event = 0;
        switch ($wdMask) {
            case IN_MODIFY:
                $event =  FilesystemEvent::MODIFY;
                break;
            case IN_CREATE:
                $event =  FilesystemEvent::CREATE;
                break;
            case IN_DELETE:
                $event =  FilesystemEvent::DELETE;
                break;
        }

        $path = $resource.DIRECTORY_SEPARATOR.$wdName;
        if($event & FilesystemEvent::CREATE){
            $tracker->checkPath($path);
        }elseif($event==FilesystemEvent::MODIFY){
            $tracker->checkPath($path);
        }elseif($event & FilesystemEvent::DELETE){
            if($tracker->has($path)){
                $tracker->checkPath($path);
            }else{
                $tracker->addChangeSet($path,$event);
            }
        }
    }

    private function trackNewDir($newPath)
    {
        if(!is_dir($newPath)) return;
        $tracker = $this->tracker;
        $flags = \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveDirectoryIterator($newPath, $flags);
        $iterator = new \RecursiveIteratorIterator(
            $iterator, \RecursiveIteratorIterator::SELF_FIRST
        );

        $tracker->checkPath($newPath);
        foreach ($iterator as $path) {
            clearstatcache(true,$path);
            $tracker->checkPath($path);
        }
    }
}