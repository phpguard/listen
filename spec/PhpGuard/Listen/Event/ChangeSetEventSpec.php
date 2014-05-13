<?php

namespace spec\PhpGuard\Listen\Event;

use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Resource\SplFileInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeSetEventSpec extends ObjectBehavior
{
    private $cSet;
    private $cInfo;
    private $cEvent;
    function let($event)
    {
        $this->cInfo = SplFileInfo::createFromBaseDir(getcwd(),__FILE__);

        $event->beADoubleOf('PhpGuard\Listen\Event\FilesystemEvent',array(
            $this->cInfo,
        ));

        $this->beConstructedWith(array($event));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Event\ChangeSetEvent');
    }

    function it_getFiles_return_empty_array_by_default()
    {
        $this->beConstructedWith(array());
        $this->getFiles()->shouldReturn(array());
    }

    function it_getFiles_returns_the_list_of_file_for_current_changeset($event)
    {
        $event->getResource()
            ->shouldBeCalled()
            ->willReturn('resource')
        ;

        $this->getFiles()->shouldReturn(array(
            'resource'
        ));
    }

    function it_getEvents_returns_the_list_of_event_for_current_changeset($event)
    {
        $this->getEvents()->shouldReturn(array(
            $event
        ));
    }
}