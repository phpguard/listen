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
use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\ChangeSetEvent;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Exception\InvalidArgumentException;
use PhpGuard\Listen\Exception\RuntimeException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Finder\SplFileInfo;
use PhpGuard\Listen\Util\PathUtil;

/**
 * Class Listener
 *
 */
class Listener implements LoggerAwareInterface
{
    private $eventMask;

    /**
     * Set paths to listen
     * @var array
     */
    private $paths = array();

    private $patterns = array();

    private $ignores = array(
        'vendor',
    );

    private $callback;

    private $changeSet = array();

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var int
     */
    private $latency = 1000000;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $listen = true;

    private $alwaysNotify = false;

    public function __construct($paths=array())
    {
        $this->eventMask = FilesystemEvent::ALL;
        $this->to($paths);
    }

    public function to($paths)
    {
        if(!is_array($paths)){
            $paths = array($paths);
        }

        foreach($paths as $path){
            $this->addPath($path);
        }

        return $this;
    }

    public function event($eventMask)
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

    public function ignores($ignores)
    {
        if(!is_array($ignores)){
            $ignores = array($ignores);
        }

        $this->ignores = array_merge($this->ignores,$ignores);

        return $this;
    }

    public function patterns($pattern)
    {
        if(!is_array($pattern)){
            $pattern = array($pattern);
        }

        $this->patterns = array_merge($this->patterns,$pattern);

        return $this;
    }

    public function callback($callback)
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

    /**
     * @param int $latency
     *
     * @return Listener
     */
    public function latency($latency)
    {
        if($latency <= 1){
            $latency = $latency * 1000000;
        }
        $this->latency = doubleval($latency);
        return $this;
    }

    public function start()
    {
        if(count($this->paths) < 1){
            throw new RuntimeException(
                sprintf(
                    'Can not start with an empty directory. '
                    .'You have to set directory to watch first with Listener::to()'
                )
            );
        }

        $this->listen = true;

        while($this->listen){
            usleep($this->latency);
            $this->evaluate();
        }
    }

    public function evaluate()
    {
        if(!isset($this->adapter)){
            $this->adapter = Listen::getDefaultAdapter();
            $this->adapter->initialize($this);
        }

        if($this->logger){
            $this->adapter->setLogger($this->logger);
        }

        $this->adapter->evaluate();
        $this->changeSet = $this->adapter->getChangeSet();
        $this->notify();
    }

    public function stop()
    {
        $this->listen = false;
    }

    /**
     * @return int
     */
    public function getLatency()
    {
        return $this->latency;
    }

    /**
     * @param \PhpGuard\Listen\Adapter\AdapterInterface $adapter
     *
     * @return Listener
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        $adapter->initialize($this);
        return $this;
    }

    /**
     * @return \PhpGuard\Listen\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getPaths()
    {
        return $this->paths;
    }

    public function addPath($path)
    {
        if(!is_dir($path)){
            throw new \InvalidArgumentException(sprintf(
                'The directory or file: "%s" is not readable or not exists',
                $path
            ));
        }

        if(!in_array($path,$this->paths)){
            $this->paths[] = $path;
        }
    }

    public function hasPath($path)
    {
        if(!$path instanceof SplFileInfo){
            $path = $this->createResource($path);
            if(false===$path){
                return false;
            }
        }

        if(!empty($this->patterns) && $path->isFile()){
            $retVal = false;
            foreach($this->patterns as $pattern){
                if(preg_match($pattern,$path->getRealPath())){
                    $retVal = true;
                    break;
                }
                if(preg_match($pattern,$path->getRelativePathname())){
                    $retVal = true;
                    break;
                }
            }
            return $retVal;
        }
        return true;
    }

    public function getEventMask()
    {
        return $this->eventMask;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getIgnores()
    {
        return $this->ignores;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function notify()
    {
        if(!$this->alwaysNotify && empty($this->changeSet)){
            return;
        }
        $event = new ChangeSetEvent($this,$this->changeSet);
        array_map(
            $this->callback,
            array($event)
        );
    }

    public function alwaysNotify($value)
    {
        $this->alwaysNotify = $value;
        return $this;
    }

    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param   LoggerInterface $logger
     * @return  Listener
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function log($message,$level=LogLevel::DEBUG,$context=array())
    {
        $this->logger->log($level,$message,$context);
    }

    public function createResource($path)
    {
        if(!is_readable($path)){
            throw new InvalidArgumentException(sprintf(
                'The path "%s" is not exists or not readable.',
                $path
            ));
        }

        foreach($this->paths as $baseDir){
            $baseDirLen = strlen($baseDir);

            if($baseDir!==substr($path,0,$baseDirLen)){
                // not own this path, should continue
                continue;
            }
            $path = PathUtil::createSplFileInfo($baseDir,$path);
            return $path;
        }

        return false;
    }
}
