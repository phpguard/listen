<?php

namespace PhpGuard\Listen;

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Exception\InvalidArgumentException;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Listener
 *
 */
class Listener
{
    private $eventMask;

    private $path;

    private $patterns = array();

    private $ignores = array();

    private $callback;

    public function setEventMask($eventMask)
    {
        $state = $eventMask & FilesystemEvent::CREATE
            | $eventMask & FilesystemEvent::DELETE
            | $eventMask & FilesystemEvent::MODIFY
            | $eventMask & FilesystemEvent::ALL
        ;
        if(false==$state){
            throw new \InvalidArgumentException(sprintf(
                'Event mask is invalid.'
            ));
        }
        $this->eventMask = $eventMask;
    }

    public function getEventMask()
    {
        return $this->eventMask;
    }

    public function setPath($path)
    {
        if(!is_dir($path)){
            throw new \InvalidArgumentException(sprintf(
                'The directory or file: "%s" is not readable or not exists',
                $path
            ));
        }
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPatterns($pattern)
    {
        if(!is_array($pattern)){
            $pattern = array($pattern);
        }
        $this->patterns = array_merge($this->patterns,$pattern);

        return $this;
    }

    public function setIgnores($ignores)
    {
        if(!is_array($ignores)){
            $ignores = array($ignores);
        }

        $this->ignores = array_merge($this->ignores,$ignores);

        return $this;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getIgnores()
    {
        return $this->ignores;
    }

    public function setCallback($callback)
    {
        if(!is_callable($callback)){
            throw new InvalidArgumentException(sprintf(
                'Listener callback should be callable. You passed "%s" type',
                gettype($callback)
            ));
        }
        $this->callback = $callback;

        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function hasPath(SplFileInfo $file)
    {
        return true;
    }
}
