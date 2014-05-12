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
use PhpGuard\Listen\Listener;
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

    private $directories = array();

    private $files = array();

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function scan(Listener $listener = null)
    {
        foreach($listener->getPaths() as $path){
            if(is_file($path)){
                // watching on single file
                $resource = new FileResource($path);
                $this->adapter->watch($resource);
            }else{
                $this->initDir($path,$listener);
            }
        }
    }

    public function hasResource($resourceID)
    {
        return array_key_exists($resourceID,$this->map);
    }

    /**
     * @param   string  $resourceID
     * @return  ResourceInterface
     */
    public function getResource($resourceID)
    {
        return $this->map[$resourceID];
    }
    public function addResource(ResourceInterface $resource)
    {
        $id = $resource->getID();
        if(array_key_exists($id,$this->map)){
            return;
        }
        $this->map[$id] = $resource;
    }

    public function remove(ResourceInterface $resource)
    {
        if(!$this->hasResource($resource->getID())){
            return;
        }
        unset($this->map[$resource->getID()]);
    }

    private function initDir($path,Listener $listener = null)
    {
        if(!is_dir($path)) return;
        $finder = Finder::create();
        $finder
            ->notPath('vendor')
        ;

        $rootSPL = new SplFileInfo($path,'','');
        $rootResource = new DirectoryResource($rootSPL);

        $this->addResource($rootResource);

        /* @var \PhpGuard\Listen\Resource\DirectoryResource $lastDir */
        /* @var \Symfony\Component\Finder\SplFileInfo $spl */
        foreach($finder->in($path) as $spl)
        {
            if(is_dir($spl)){
                $resource = new DirectoryResource($spl);
            }elseif(is_file($spl)){
                $resource = new FileResource($spl);
            }

            if(!is_null($listener)){
                if(!$listener->hasPath($spl)){
                    continue;
                }
            }
            $parent = dirname($spl);
            $parentID = md5('d'.$parent);
            $this->map[$parentID]->addChild($resource);

            $this->addResource($resource);
        }

        foreach($this->map as $resource){
            $this->adapter->watch($resource);
        }
    }

}
