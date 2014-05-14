<?php

namespace spec\PhpGuard\Listen\Adapter;

require_once __DIR__.'/../MockFileSystem.php';

use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


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
}