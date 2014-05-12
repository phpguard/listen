<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Resource;
use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Exception\InvalidArgumentException;
use PhpGuard\Listen\Watcher;
use Symfony\Component\Finder\Finder;

/**
 * Class ResourceManager
 */
class ResourceManager
{
    private $map = array();

    /**
     * @var \PhpGuard\Listen\Adapter\AdapterInterface
     */
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function scan(Watcher $watcher)
    {
        $path = $watcher->getPath();
        if(is_file($path)){
            // watching on single file
            $resource = new FileResource($path);
            $this->adapter->watch($resource);
        }else{
            $this->initDir($path,$watcher);
        }

    }

    public function hasResource($resourceID)
    {
        return array_key_exists($resourceID,$this->map);
    }

    public function addResource(ResourceInterface $resource)
    {
        $id = $resource->getID();
        if(!array_key_exists($id,$this->map)){
            $this->map[$id] = $resource;
        }

    }

    private function initDir($path,Watcher $watcher = null)
    {
        if(!is_dir($path)) return;
        $finder = Finder::create();
        $finder
            ->notPath('vendor')
        ;
        foreach($finder->in($path) as $spl)
        {
            if(is_dir($spl)){
                $resource = new DirectoryResource($spl);
            }elseif(is_file($spl)){
                $resource = new FileResource($spl);
            }
            if(!is_null($watcher)){
                if(!$watcher->hasPath($spl)){
                    continue;
                }
            }
            $this->adapter->watch($resource);
            $this->addResource($resource);
        }
    }

}
