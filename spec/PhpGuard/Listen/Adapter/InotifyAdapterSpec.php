<?php

namespace spec\PhpGuard\Listen\Adapter;

require_once realpath(__DIR__.'/../MockFileSystem.php');

use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use spec\PhpGuard\Listen\MockFileSystem as MFS;

class InotifyAdapterSpec extends ObjectBehavior
{
    function let(Listener $listener)
    {
        MFS::mkdir(MFS::$tmpDir);

        $listener->getPaths()
            ->willReturn(array(MFS::$tmpDir));
        $listener->getIgnores()
            ->willReturn(array());
        $listener->getPatterns()
            ->willReturn(array());
    }

    function letgo()
    {
        MFS::cleanDir(MFS::$tmpDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\InotifyAdapter');
    }

    function it_is_an_adapter()
    {
        $this->shouldImplement('PhpGuard\Listen\Adapter\AdapterInterface');
    }

    function it_should_log_message_with_level_debug_as_default(LoggerInterface $logger)
    {
        $logger->log(LogLevel::DEBUG,'message',array())
            ->shouldBeCalled()
        ;
        $this->setLogger($logger);
        $this->log('message');
    }

    function it_should_listen_to_filesystem_create_event(Listener $listener)
    {
        $newFile = MFS::$tmpDir.'/foo.txt';

        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(MFS::$tmpDir))
        ;
        $listener->hasPath(Argument::any())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->initialize($listener);

        touch($newFile);

        $this->evaluate();
        $this->getChangeSet()->shouldHaveCount(1);
    }

    function it_should_listen_to_filesystem_update_event(Listener $listener)
    {
        $newFile = MFS::$tmpDir.'/update.txt';
        MFS::cleanDir(MFS::$tmpDir);
        MFS::mkdir(MFS::$tmpDir);
        touch($newFile);

        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(MFS::$tmpDir))
        ;
        $listener->hasPath(Argument::any())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->beConstructedWith(null);
        $this->initialize($listener);

        file_put_contents($newFile,'Hello WOrld',LOCK_EX);

        $this->evaluate();
        $this->getChangeSet()->shouldHaveCount(1);

    }

    function it_should_listen_to_file_system_delete_event(Listener $listener)
    {
        $newFile = MFS::$tmpDir.'/delete.txt';
        touch($newFile);

        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(MFS::$tmpDir))
        ;
        $listener->hasPath(Argument::any())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->beConstructedWith(null);
        $this->initialize($listener);

        unlink($newFile);
        $this->evaluate();

        $this->getChangeSet()->shouldHaveCount(1);
    }
}