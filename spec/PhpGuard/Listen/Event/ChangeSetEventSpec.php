<?php

namespace spec\PhpGuard\Listen\Event;

use PhpGuard\Listen\Event\FilesystemEvent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeSetEventSpec extends ObjectBehavior
{
    private $cInfo;
    function let($event)
    {
        $subPathName = str_replace(getcwd(),'',__FILE__);
        $subPath = dirname($subPathName);
        $this->cInfo = new SplFileInfo(__FILE__,$subPath,$subPathName);

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