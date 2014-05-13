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

    private $listeners = array();

    /**
     * @var Tracker
     */
    private $tracker;

    public function __construct()
    {
        $this->tracker = new Tracker();
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

    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Initialize a listener
     *
     * @param   Listener $listener
     * @return  void
     */
    public function initialize(Listener $listener)
    {
        $this->tracker->add($listener);
        $this->listeners[] = $listener;
    }

    /**
     * @return array
     */
    public function evaluate()
    {
        $this->tracker->refresh();
        /* @var Listener $listener */
        foreach($this->listeners as $listener)
        {
            if(count($listener->getChangeSet())>0){
                $listener->notifyCallback();
            }
        }
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
}