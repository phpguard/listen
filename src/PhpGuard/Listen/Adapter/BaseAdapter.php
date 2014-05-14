<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Adapter;

use PhpGuard\Listen\Resource\TrackedObject;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

abstract class BaseAdapter implements AdapterInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function log($message,array $context = array(),$level=LogLevel::DEBUG)
    {
        if(!isset($this->logger)){
            return;
        }
        $this->logger->log($level,$message,$context);
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

    public function watch(TrackedObject $tracked)
    {
        $this->log(sprintf(
            'Added new path to watch'
        ),array('path'=>(string)$tracked->getResource()),LogLevel::DEBUG);
    }

    public function unwatch(TrackedObject $tracked)
    {
        $this->log(
            'Unwatch tracked object'
            ,array('path'=>(string)$tracked->getResource()),LogLevel::DEBUG);
        return;
    }

} 