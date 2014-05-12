<?php

namespace spec\PhpGuard\Listen;

use PhpGuard\Listen\Event\FilesystemEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
        $this->setPatterns('any')->shouldReturn($this);
        $this->getPatterns()->shouldReturn(array('any'));
    }

    function its_getIgnores_returns_an_empty_array_by_default()
    {
        $this->getIgnores()->shouldReturn(array());
    }

    function its_ignores_should_be_mutable()
    {
        $this->ignores('any')->shouldReturn($this);
        $this->getIgnores()->shouldReturn(array('any'));
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
}