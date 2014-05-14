<?php

namespace spec\PhpGuard\Listen\Adapter;

require_once __DIR__.'/../MockFileSystem.php';

use PhpGuard\Listen\Adapter\Basic\WatchMap;
use PhpGuard\Listen\Resource\ResourceInterface;
use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;


use spec\PhpGuard\Listen\MockFileSystem as MFS;

class BasicAdapterSpec extends ObjectBehavior
{
    private $tmpDir;

    function let(Listener $listener)
    {
        MFS::mkdir(MFS::$tmpDir);

        $listener->hasPath(Argument::any())
            ->willReturn(true)
        ;

        $listener
            ->getPatterns()
            ->willReturn(array())
        ;

        $listener->getIgnores()
            ->willReturn(array())
        ;
    }

    function letgo()
    {
        MFS::cleanDir(MFS::$tmpDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\BasicAdapter');
    }

    function its_initialize_should_add_listener_to_tracker(Listener $listener)
    {
        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(__DIR__))
        ;

        $this->initialize($listener);
    }
}