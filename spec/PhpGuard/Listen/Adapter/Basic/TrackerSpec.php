<?php

namespace spec\PhpGuard\Listen\Adapter\Basic;

require_once __DIR__.'/../../MockFileSystem.php';
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\TrackedResource;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use spec\PhpGuard\Listen\MockFileSystem as MFS;

class TrackerSpec extends ObjectBehavior
{
    function let(Listener $listener)
    {
        MFS::cleanDir(MFS::$tmpDir);
        MFS::mkdir(MFS::$tmpDir);
        $listener->getPaths()
            ->willReturn(MFS::$tmpDir);
        //$resource->getListeners()->attach($listener);
    }

    function letgo()
    {
        MFS::cleanDir(MFS::$tmpDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Basic\Tracker');
    }

    function it_should_provide_a_way_to_add_tracked_resource(Listener $listener)
    {
        touch($f1 = MFS::$tmpDir.'/file.txt');

        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(MFS::$tmpDir))
        ;

        $this->add($listener);
    }
}