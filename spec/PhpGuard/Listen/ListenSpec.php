<?php

namespace spec\PhpGuard\Listen;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listen;
use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MockListen extends Listen
{
    public function start()
    {
        $this->initializeAdapters();
    }

    public function setAdapterInitialized($value)
    {
        $this->adapterInitialized = $value;
    }
}

class ListenSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher, AdapterInterface $adapter)
    {
        //$this->beConstructedWith($dispatcher,$adapter);
        $this->beAnInstanceOf(__NAMESPACE__.'\\MockListen',array($dispatcher,$adapter));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Listen');
    }

    function it_should_create_dispatcher_automatically()
    {
        $this->beConstructedWith(null,null);
        $this->getDispatcher()
            ->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher');
    }

    function it_should_create_adapter_automatically()
    {
        $this->beConstructedWith(null,null);
        $this->getAdapter()
            ->shouldBeAnInstanceOf('PhpGuard\\Listen\\Adapter\\AdapterInterface')
        ;
    }

    function it_should_create_listener_properly(AdapterInterface $adapter)
    {
        $listener = $this->to(getcwd());
        $listener->getEventMask()->shouldReturn(FilesystemEvent::ALL);
        $listener->getPaths()->shouldReturn(array(getcwd()));
        $listener->getPatterns()->shouldReturn(array());
        $listener->getIgnores()->shouldReturn(array());
    }

    function it_start_should_initialize_adapter_first(AdapterInterface $adapter,Listener $listener)
    {
        $listener = $this->to(__DIR__);

        $adapter->initialize($listener)->shouldBeCalled(2);
        $this->start();
    }

    function it_start_should_not_initialize_adapter_if_already_initialized($adapter)
    {
        $listener = $this->to(__DIR__);
        $this->setAdapterInitialized(true);
        $adapter->initialize(Argument::any())->shouldNotBeCalled();
        $this->start();
    }
}