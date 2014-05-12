<?php

namespace spec\PhpGuard\Listen\Adapter\Basic;

use PhpGuard\Listen\Adapter\Basic\WatchMap;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class BasicAdapterSpec extends ObjectBehavior
{
    function let(WatchMap $watchMap)
    {
        $this->beConstructedWith($watchMap);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Basic\PoolingAdapter');
    }

    function it_should_implement_the_PSR_LoggerAwareInterface()
    {
        $this->shouldImplement('Psr\Log\LoggerAwareInterface');
    }

    function it_should_initialize_listener(Listener $listener,WatchMap $watchMap)
    {
        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(__DIR__))
        ;

        $listener->hasPath(Argument::any())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->initialize($listener);
    }

    function it_should_watch_resource_changes(ResourceInterface $resource)
    {
        $resource->getID()->willReturn('any');
        $resource->getChecksum()->willReturn('any');
        $resource->getTrackingID()->willReturn(null);

        $resource
            ->setTrackingID('any')
            ->shouldBeCalled()
        ;
        $this->watch($resource);
    }

    function it_should_log_message_with_level_debug_as_default(LoggerInterface $logger)
    {
        $logger->log(LogLevel::DEBUG,'message',array())
            ->shouldBeCalled()
        ;
        $this->setLogger($logger);
        $this->log('message');
    }
}