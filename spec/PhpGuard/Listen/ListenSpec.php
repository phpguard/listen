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

    function it_should_listen_to_directory_change_properly(AdapterInterface $adapter)
    {
        $listener = $this->to(getcwd());
        $listener->getEventMask()->shouldReturn(FilesystemEvent::ALL);
        $listener->getPaths()->shouldReturn(array(getcwd()));
        $listener->getPatterns()->shouldReturn(array());
        $listener->getIgnores()->shouldReturn(array());
    }
}