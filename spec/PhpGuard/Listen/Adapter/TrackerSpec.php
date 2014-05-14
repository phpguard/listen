<?php

namespace spec\PhpGuard\Listen\Adapter;

use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Util\PathUtil;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;

class TrackerSpec extends ObjectBehavior
{
    function let(Listener $listener,TrackedObject $tracked, ResourceInterface $resource)
    {
        $listener->getPaths()
            ->willReturn(array(__DIR__))
        ;
        $listener->getIgnores()
            ->willReturn(array())
        ;
        $listener->getPatterns()
            ->willReturn(array())
        ;
        $tracked->getResource()
            ->willReturn($resource);

        $listener->hasPath(Argument::any())
            ->willReturn(true);

        $this->initialize($listener);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Tracker');
    }

    function it_should_initialize_listener(Listener $listener)
    {
        $this->initialize($listener);
        $this->hasTrack(__DIR__)->shouldReturn(true);
    }

    function its_hasTrack_returns_true_if_the_path_or_id_is_registered()
    {
        $this->hasTrack(md5('d'.__DIR__))
            ->shouldReturn(true);
        $this->hasTrack(md5('f'.__FILE__))
            ->shouldReturn(true);
    }

    function its_hasTrack_returns_false_if_the_path_is_not_registered(TrackedObject $tracked,ResourceInterface $resource)
    {
        $this->hasTrack('/tmp')
            ->shouldReturn(false);
        $this->hasTrack(getcwd())
            ->shouldReturn(false);
    }

    function its_hasTrack_should_convert_dirname_or_filename_into_track_id()
    {

        $this->hasTrack(__DIR__)
            ->shouldReturn(true)
        ;

        $this->hasTrack(__FILE__)
            ->shouldReturn(true)
        ;

        $this->hasTrack('/tmp')
            ->shouldReturn(false);
    }

    function its_hasTrack_should_convert_SplFileInfo_object_to_track_id()
    {
        $spl = new SplFileInfo(__DIR__,'','');
        $this->hasTrack($spl)
            ->shouldReturn(true)
        ;
    }

    function its_getTrack_returns_true_if_the_trackID_is_registered($listener,$tracked)
    {
        $id = PathUtil::createPathID('/tmp');
        $tracked->getID()
            ->shouldBeCalled()
            ->willReturn($id)
        ;
        $this->addTrack($tracked);
        $this->getTrack($id)
            ->shouldReturn($tracked)
        ;
    }

    function its_getTrack_should_convert_dirname_or_filename_into_track_id($tracked)
    {
        $id = PathUtil::createPathID('/tmp');
        $tracked->getID()
            ->shouldBeCalled()
            ->willReturn($id)
        ;
        $this->addTrack($tracked);
        $this->getTrack('/tmp')
            ->shouldReturn($tracked)
        ;
    }

    function its_getTrack_should_throws_when_the_trackID_is_not_registered()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringGetTrack('foo');
    }


}