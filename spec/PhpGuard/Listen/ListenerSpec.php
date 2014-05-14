<?php

namespace spec\PhpGuard\Listen;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Listener');
    }

    function it_should_set_an_event_to_listen()
    {
        $this->event(FilesystemEvent::ALL);
        $this->getEventMask()->shouldReturn(FilesystemEvent::ALL);
    }

    function it_should_throws_when_setting_event_with_invalid_value()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringEvent(0);
    }

    function it_should_set_a_directory_to_listen()
    {
        $this->to(getcwd())->shouldReturn($this);
        $this->getPaths()->shouldReturn(array(getcwd()));
    }

    function its_addPath_throws_when_the_path_does_not_exists()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringAddPath('/foo/bar');
    }

    function its_getPatterns_returns_empty_array_by_default()
    {
        $this->getPatterns()->shouldReturn(array());
    }

    function its_patterns_should_be_mutable()
    {
        $this->patterns('any')->shouldReturn($this);
        $this->getPatterns()->shouldReturn(array('any'));
    }

    function its_getIgnores_returns_default_ignored_files()
    {
        $this->getIgnores()->shouldReturn(array(
            'vendor'
        ));
    }

    function its_ignores_should_be_mutable()
    {
        $this->ignores('any')->shouldReturn($this);
        $this->getIgnores()->shouldReturn(array('vendor','any'));
    }

    function it_should_set_callback()
    {
        $callback = function(){};
        $this->callback($callback)->shouldReturn($this);
        $this->getCallback()->shouldReturn($callback);
    }

    function it_should_throws_when_setting_callback_with_uncallable_value()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringCallback('foo')
        ;
    }

    function its_adapter_should_be_mutable(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter)->shouldReturn($this);
        $this->getAdapter()->shouldReturn($adapter);
    }

    function its_latency_should_be_mutable()
    {
        $this->latency(1000)->shouldReturn($this);
        $this->getLatency()->shouldReturn(doubleval(1000));
    }

    function its_latency_value_should_be_converted_to_microseconds()
    {
        $this->latency(0.01)->shouldReturn($this);
        $this->getLatency()->shouldReturn(doubleval(10000));

        $this->latency(0.25)->shouldReturn($this);
        $this->getLatency()->shouldReturn(doubleval(250000));
    }

    function it_should_implement_the_PSR_LoggerAwareInterface()
    {
        $this->shouldImplement('Psr\Log\LoggerAwareInterface');
    }

    function its_should_log_message_with_level_debug_as_default(LoggerInterface $logger)
    {
        $logger->log(LogLevel::DEBUG,'message',array())
            ->shouldBeCalled()
        ;
        $this->setLogger($logger);
        $this->log('message');
    }

    function it_throws_when_starting_without_any_paths()
    {
        $this->shouldThrow('RuntimeException')
            ->duringStart();
    }
}