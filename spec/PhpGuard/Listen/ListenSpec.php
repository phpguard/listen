<?php

namespace spec\PhpGuard\Listen;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ListenSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher, AdapterInterface $adapter)
    {
        $this->beConstructedWith($dispatcher,$adapter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Listen');
    }

    function it_should_watch_a_directory(AdapterInterface $adapter)
    {
        $options = array();

        $adapter->initialize(Argument::any())
            ->shouldBeCalled()
        ;

        $watcher = $this->watch(getcwd(),$options);
        $watcher->getEventMask()->shouldReturn(FilesystemEvent::ALL);
        $watcher->getPath()->shouldReturn(getcwd());
    }
}