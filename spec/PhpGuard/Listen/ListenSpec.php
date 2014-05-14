<?php

namespace spec\PhpGuard\Listen;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listen;
use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class ListenSpec extends ObjectBehavior
{
    function it_should_create_new_listener()
    {
        $this->to(__DIR__)->shouldHaveType('PhpGuard\\Listen\\Listener');
    }

    function it_should_initialize_adapter_for_listener()
    {
        $listener = $this->to(__DIR__);
        $listener->getAdapter()
            ->shouldImplement('PhpGuard\\Listen\\Adapter\\AdapterInterface')
        ;
    }


}