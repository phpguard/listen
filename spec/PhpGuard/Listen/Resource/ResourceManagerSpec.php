<?php

namespace spec\PhpGuard\Listen\Resource;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Watcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResourceManagerSpec extends ObjectBehavior
{
    function let(AdapterInterface $adapter,Watcher $watcher)
    {
        $watcher->getPath()->willReturn(__DIR__);
        $watcher->hasPath(Argument::any())->willReturn(true);
        $this->beConstructedWith($adapter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Resource\ResourceManager');
    }

    function it_addResource_should_map_resource_based_on_resource_id(ResourceInterface $resource)
    {
        $resource->getID()
            ->shouldBeCalled()
            ->willReturn('any')
        ;

        $this->addResource($resource);
        $this->hasResource('any')->shouldReturn(true);
    }

    function it_scan_should_process_directory(AdapterInterface $adapter,Watcher $watcher)
    {
        $adapter->watch(Argument::any())
            ->shouldBeCalled()
        ;

        $watcher
            ->hasPath(Argument::any())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->scan($watcher);
    }

    function it_scan_should_process_file(AdapterInterface $adapter,Watcher $watcher)
    {
        $watcher->getPath()
            ->willReturn(__FILE__)
        ;
        $adapter->watch(Argument::any())->shouldBeCalled();
        $this->scan($watcher);
    }

    function it_hasResource_returns_true_if_path_mapped(ResourceInterface $resource)
    {
        $resource->getID()->willReturn('any');
        $this->addResource($resource);
        $this->hasResource('any')->shouldReturn(true);
    }

    function it_hasResource_returns_false_if_path_is_not_mapped()
    {
        $this->hasResource('foobar')->shouldReturn(false);
    }
}