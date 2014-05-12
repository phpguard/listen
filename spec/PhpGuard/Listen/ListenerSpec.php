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

    function its_event_mask_should_be_mutable()
    {
        $this->setEventMask(FilesystemEvent::ALL);
        $this->getEventMask()->shouldReturn(FilesystemEvent::ALL);
    }

    function its_getEventMask_throws_when_setting_an_event_mask_with_invalid_value()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringSetEventMask(0);
    }

    function its_paths_should_be_mutable()
    {
        $this->setPaths(getcwd())->shouldReturn($this);
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
        $this->setIgnores('any')->shouldReturn($this);
        $this->getIgnores()->shouldReturn(array('any'));
    }

    function its_callback_should_be_mutable()
    {
        $callback = function(){};
        $this->setCallback($callback)->shouldReturn($this);
        $this->getCallback()->shouldReturn($callback);
    }

    function its_setCallback_throws_if_passed_argument_is_not_callable()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringSetCallback('foo')
        ;
    }
}