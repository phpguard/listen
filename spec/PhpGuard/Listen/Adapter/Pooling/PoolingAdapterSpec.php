<?php

namespace spec\PhpGuard\Listen\Adapter\Pooling;

use PhpGuard\Listen\Adapter\Pooling\WatchMap;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Watcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PoolingAdapterSpec extends ObjectBehavior
{
    function let(WatchMap $watchMap)
    {
        $this->beConstructedWith($watchMap);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Pooling\PoolingAdapter');
    }

    function it_should_implement_the_PSR_LoggerAwareInterface()
    {
        $this->shouldImplement('Psr\Log\LoggerAwareInterface');
    }

    function it_should_initialize_watcher(Watcher $watcher,WatchMap $watchMap)
    {
        $watcher->getPath()
            ->shouldBeCalled()
            ->willReturn(__DIR__)
        ;

        $watcher->hasPath(Argument::any())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->initialize($watcher);
    }

    function it_should_watch_resource_changes(ResourceInterface $resource)
    {
        $resource->getID()->willReturn('any');
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