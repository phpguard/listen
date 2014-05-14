<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Event;
use PhpGuard\Listen\Listener;

/**
 * Class ChangeSetEvent
 *
 */
class ChangeSetEvent
{
    private $changeSet = array();

    private $files = array();

    /**
     * @var \PhpGuard\Listen\Listener
     */
    private $listener;

    public function __construct(Listener $listener,array $changeSet = array())
    {
        $this->changeSet = $changeSet;

        /* @var FilesystemEvent $event */
        foreach($changeSet as $event){
            $this->files[] = $event->getResource();
        }

        $this->listener = $listener;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getEvents()
    {
        return $this->changeSet;
    }

    /**
     * @return Listener
     */
    public function getListener()
    {
        return $this->listener;
    }
}