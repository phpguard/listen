<?php

namespace spec\PhpGuard\Listen\Adapter\Basic;

require_once __DIR__.'/../../MockFileSystem.php';

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

        // TODO: move this path into temp dir
        $listener->getPaths()
            ->willReturn(array(MFS::$tmpDir))
        ;
        $listener->hasPath(Argument::any())
            ->willReturn(true)
        ;

        $listener
            ->getPatterns()
            ->willReturn(array())
        ;
    }

    function letgo()
    {
        MFS::cleanDir(MFS::$tmpDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Basic\BasicAdapter');
    }

    function it_should_implement_the_PSR_LoggerAwareInterface()
    {
        $this->shouldImplement('Psr\Log\LoggerAwareInterface');
    }

    function its_initialize_should_add_listener_to_tracker(Listener $listener)
    {
        $listener->getPaths()
            ->shouldBeCalled()
            ->willReturn(array(__DIR__))
        ;

        $this->initialize($listener);
    }

    function its_getListeners_returns_all_the_registered_listener($listener)
    {
        $this->getListeners()->shouldReturn(array());
        $this->initialize($listener);
        $this->getListeners()->shouldReturn(array($listener));
    }

    function its_log_should_log_message_with_level_debug_as_default(LoggerInterface $logger)
    {
        $logger->log(LogLevel::DEBUG,'message',array())
            ->shouldBeCalled()
        ;
        $this->setLogger($logger);
        $this->log('message');
    }

    function its_evaluate_should_notify_listener_callback_after_evaluated(Listener $listener)
    {
        $file1 = MFS::$tmpDir.'/file1.txt';

        $listener->getChangeSet()
            ->willReturn(array('any'))
        ;

        $listener->notifyCallback()
            ->shouldBeCalled();

        $listener->setChangeSet(Argument::any())
            ->shouldBeCalled();

        $this->initialize($listener);

        touch($file1);

        $this->evaluate();
    }

    function its_evaluate_should_not_notify_listeners_callback_when_changeset_is_empty(Listener $listener)
    {
        $listener->getPaths()->willReturn(array(
            __DIR__
        ));

        $listener->hasPath(Argument::any())
            ->willReturn(true);
        $listener->getChangeSet()
            ->willReturn(array())
        ;
        $listener->setChangeSet(array())
            ->shouldBeCalled();
        $this->initialize($listener);

        $listener->notifyCallback()
            ->shouldNotBeCalled();

        $this->evaluate();
    }

}