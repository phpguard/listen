<?php

namespace spec\PhpGuard\Listen\Event;

use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Resource\SplFileInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FilesystemEventSpec extends ObjectBehavior
{
    protected $pathInfo;
    function let($pathInfo)
    {
        $cwd = getcwd();
        $subpathname = str_replace($cwd,'',__FILE__);
        $subpath = dirname($subpathname);
        $this->pathInfo = new SplFileInfo(__FILE__,$subpath,$subpathname);
        $this->beConstructedWith($this->pathInfo,FilesystemEvent::CREATE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Event\FilesystemEvent');
    }

    function it_should_translate_type_into_human_readable_type()
    {
        $this->getHumanType(FilesystemEvent::CREATE)
            ->shouldReturn('create')
        ;
    }

    function it_throws_when_creating_event_with_invalid_type()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->during('__construct',array($this->pathInfo,'invalid'));
    }

    function it_should_return_resource_for_event()
    {
        $this->getResource()->shouldReturn($this->pathInfo);
    }

    function it_should_return_the_type_for_create_event()
    {
        $this->beConstructedWith($this->pathInfo,FilesystemEvent::CREATE);
        $this->getType()->shouldReturn(FilesystemEvent::CREATE);
        $this->isCreate()->shouldReturn(true);
        $this->getHumanType()->shouldReturn('create');
    }

    function it_should_return_the_type_for_modify_event()
    {
        $this->beConstructedWith($this->pathInfo,FilesystemEvent::MODIFY);
        $this->getHumanType()->shouldReturn('modify');
        $this->isModify()->shouldReturn(true);
    }

    function it_should_return_the_type_for_delete_event()
    {
        $this->beConstructedWith($this->pathInfo,FilesystemEvent::DELETE);
        $this->getHumanType()->shouldReturn('delete');
        $this->isDelete()->shouldReturn(true);
    }
}