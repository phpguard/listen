<?php

namespace spec\PhpGuard\Listen\Adapter;

require_once realpath(__DIR__."/../MockFileSystem.php");

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Resource\TrackedObject;
use PhpGuard\Listen\Util\PathUtil;
use PhpGuard\Listen\Resource\FileResource;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Symfony\Component\Finder\SplFileInfo;

use spec\PhpGuard\Listen\MockFileSystem as MFS;

class TrackerSpec extends ObjectBehavior
{
    protected $testFile;

    function let(AdapterInterface $adapter,Listener $listener,TrackedObject $tracked, ResourceInterface $resource)
    {
        MFS::mkdir(MFS::$tmpDir);
        touch($this->testFile = MFS::$tmpDir.'/test.txt');
        $listener->getPaths()
            ->willReturn(array(MFS::$tmpDir))
        ;

        $listener->getIgnores()
            ->willReturn(array())
        ;
        $listener->getPatterns()
            ->willReturn(array())
        ;
        $listener->hasPath(Argument::any())
            ->willReturn(true);

        $tracked->getID()
            ->willReturn(PathUtil::createPathID($this->testFile))
        ;
        $tracked->getResource()
            ->willReturn($resource);
        $tracked->getResource()
            ->willReturn($resource);

        $resource->getResource()
            ->willReturn(PathUtil::createSplFileInfo(MFS::$tmpDir,$this->testFile))
        ;
        $resource->__toString()
            ->willReturn($this->testFile);

        $this->beConstructedWith($adapter);
        $this->fileOnly(false);
        $this->add($tracked);
        $this->initialize($listener);
    }

    function letgo()
    {
        MFS::cleanDir(MFS::$tmpDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Tracker');
    }

    function it_should_initialize_listener(Listener $listener)
    {
        $this->has(MFS::$tmpDir)
            ->shouldReturn(true)
        ;
        $this->has($this->testFile)->shouldReturn(true);
    }

    function its_has_returns_true_if_the_path_or_id_is_registered()
    {
        $this->has(md5('f'.$this->testFile))
            ->shouldReturn(true);
    }

    function its_has_returns_false_if_the_path_is_not_registered(TrackedObject $tracked,ResourceInterface $resource)
    {
        $this->has('/tmp')
            ->shouldReturn(false);
        $this->has(getcwd())
            ->shouldReturn(false);
    }

    function its_has_should_convert_dirname_or_filename_into_track_id()
    {
        $this->has($this->testFile)
            ->shouldReturn(true)
        ;
        $this->has('/tmp')
            ->shouldReturn(false);
    }

    function its_has_should_convert_SplFileInfo_object_to_track_id()
    {
        $spl = new SplFileInfo($this->testFile,'','');
        $this->has($spl)
            ->shouldReturn(true)
        ;
    }

    function its_get_returns_true_if_the_trackID_is_registered($listener,$tracked)
    {
        MFS::mkdir($path=MFS::$tmpDir.'/path');
        $id = PathUtil::createPathID($path);
        $tracked->getID()
            ->shouldBeCalled()
            ->willReturn($id)
        ;
        $this->add($tracked);
        $this->get($id)
            ->shouldReturn($tracked)
        ;
    }

    function its_get_should_convert_dirname_or_filename_into_track_id($tracked)
    {
        $id = PathUtil::createPathID('/tmp');
        $tracked->getID()
            ->shouldBeCalled()
            ->willReturn($id)
        ;
        $this->add($tracked);
        $this->get('/tmp')
            ->shouldReturn($tracked)
        ;
    }

    function its_get_should_throws_when_the_trackID_is_not_registered()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringGet('foo');
    }

    function it_should_remove_track($tracked)
    {
        $this->remove($tracked);
        $this->has($this->testFile)->shouldReturn(false);
    }

    function its_checkPath_should_track_on_filesystem_create_event(AdapterInterface $adapter,Listener $listener)
    {
        $newFile = MFS::$tmpDir.'/new.txt';

        $listener->hasPath($newFile)
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $adapter->watch(Argument::any())
            ->shouldBeCalled()
        ;
        $this->beConstructedWith($adapter);
        $this->initialize($listener);
        touch($newFile);
        $this->checkPath($newFile);
        $this->getChangeSet()->shouldHaveCount(1);
    }

    function its_checkPath_should_track_on_filesystem_modify_event(ResourceInterface $resource,TrackedObject $tracked)
    {
        $tracked->getResource()
            ->willReturn($resource);
        $tracked->getID()
            ->willReturn(PathUtil::createPathID($this->testFile))
        ;
        $tracked->getChecksum()
            ->willReturn('old_checksum');
        $tracked->setChecksum('new_checksum')
            ->shouldBeCalled()
        ;

        $resource->isExists()
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $resource->getChecksum()
            ->shouldBeCalled()
            ->willReturn('new_checksum')
        ;

        $this->checkPath($this->testFile);
        $this->getChangeSet()->shouldHaveCount(1);
    }

    function its_checkPath_should_track_on_filesystem_delete_event(AdapterInterface $adapter,FileResource $resource,TrackedObject $tracked)
    {
        $tracked->getResource()
            ->shouldBeCalled()
            ->willReturn($resource)
        ;
        $tracked->getChecksum()
            ->shouldBeCalled()
            ->willReturn('checksum')
        ;

        $resource->isExists()
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $resource->getChecksum()
            ->willReturn('new_checksum')
            ->shouldBeCalled();
        $resource->getResource()
            ->willReturn($this->testFile);

        $adapter->unwatch($tracked)->shouldBeCalled();
        $this->checkPath($this->testFile);
        $this->getChangeSet()->shouldHaveCount(1);
    }
}