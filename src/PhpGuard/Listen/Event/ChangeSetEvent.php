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

/**
 * Class ChangeSetEvent
 *
 */
class ChangeSetEvent
{
    private $changeSet = array();

    private $files = array();

    public function __construct(array $changeSet = array())
    {
        $this->changeSet = $changeSet;

        /* @var FilesystemEvent $event */
        foreach($changeSet as $event){
            $this->files[] = $event->getResource();
        }
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getEvents()
    {
        return $this->changeSet;
    }
}