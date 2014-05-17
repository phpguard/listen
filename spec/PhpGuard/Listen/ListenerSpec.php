<?php

namespace spec\PhpGuard\Listen;

require_once __DIR__.'/MockFileSystem.php';

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\ChangeSetEvent;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Util\PathUtil;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use spec\PhpGuard\Listen\MockFileSystem as mfs;

class ListenerSpec extends ObjectBehavior
{
    static $tmpDir;

    function let()
    {
        mfs::mkdir(mfs::$tmpDir);
        $this->alwaysNotify(true);
    }

    function letgo()
    {
        mfs::cleanDir(mfs::$tmpDir);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Listener');
    }

    function it_should_set_an_event_to_listen()
    {
        $this->event(FilesystemEvent::ALL);
        $this->getEventMask()->shouldReturn(FilesystemEvent::ALL);
    }

    function it_should_throws_when_setting_event_with_invalid_value()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringEvent(0);
    }

    function it_should_set_a_directory_to_listen()
    {
        $this->to(getcwd())->shouldReturn($this);
        $this->getPaths()->shouldReturn(array(getcwd()));
    }

    function its_addPath_throws_when_the_path_does_not_exists()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringAddPath('/foo/bar');
    }

    function its_getPatterns_returns_empty_array_by_default()
    {
        $this->getPatterns()->shouldReturn(array());
    }

    function its_patterns_should_be_mutable()
    {
        $this->patterns('any')->shouldReturn($this);
        $this->getPatterns()->shouldReturn(array('any'));
    }

    function its_getIgnores_returns_default_ignored_files()
    {
        $this->getIgnores()->shouldReturn(array(
            'vendor'
        ));
    }

    function its_ignores_should_be_mutable()
    {
        $this->ignores('any')->shouldReturn($this);
        $this->getIgnores()->shouldReturn(array('vendor','any'));
    }

    function it_should_set_callback()
    {
        $callback = function(){};
        $this->callback($callback)->shouldReturn($this);
        $this->getCallback()->shouldReturn($callback);
    }

    function it_should_throws_when_setting_callback_with_uncallable_value()
    {
        $this->shouldThrow('InvalidArgumentException')
            ->duringCallback('foo')
        ;
    }

    function its_adapter_should_be_mutable(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter)->shouldReturn($this);
        $this->getAdapter()->shouldReturn($adapter);
    }

    function its_latency_should_be_mutable()
    {
        $this->latency(1000)->shouldReturn($this);
        $this->getLatency()->shouldReturn(doubleval(1000));
    }

    function its_latency_value_should_be_converted_to_microseconds()
    {
        $this->latency(0.01)->shouldReturn($this);
        $this->getLatency()->shouldReturn(doubleval(10000));

        $this->latency(0.25)->shouldReturn($this);
        $this->getLatency()->shouldReturn(doubleval(250000));
    }

    function it_should_implement_the_PSR_LoggerAwareInterface()
    {
        $this->shouldImplement('Psr\Log\LoggerAwareInterface');
    }

    function it_should_log_message_with_level_debug_as_default(LoggerInterface $logger)
    {
        $logger->log(LogLevel::DEBUG,'message',array())
            ->shouldBeCalled()
        ;
        $this->setLogger($logger);
        $this->log('message');
    }

    function it_throws_when_starting_without_any_paths()
    {
        $this->shouldThrow('RuntimeException')
            ->duringStart();
    }

    function its_createResource_returns_SplFileInfo_object_for_the_path()
    {
        mfs::mkdir($root = MFS::$tmpDir.'/root');
        mfs::mkdir($dir1 = $root.'/dir1/subdir');
        mfs::mkdir($dir2 = $root.'/dir2/subdir');
        touch($file1 = $dir1.'/file1.php');
        touch($file2 = $dir2.'/file2.php');

        $this->to($root.'/dir1');
        $this->to($root.'/dir2');

        $spl = $this->createResource($file1);
        $spl->getRelativePath()
            ->shouldReturn('subdir');
        $spl->getRelativePathname()->shouldReturn('subdir/file1.php');

        $spl = $this->createResource($file2);
        $spl->getRelativePath()
            ->shouldReturn('subdir');
        $spl->getRelativePathname()->shouldReturn('subdir/file2.php');
    }

    function its_createResource_returns_false_if_path_is_outside_watched_list()
    {
        mfs::mkdir($root = MFS::$tmpDir.'/root');
        touch($file1 = mfs::$tmpDir.'/file1.php');
        $this->to($root);

        $this->createResource(__FILE__)->shouldReturn(false);
        $this->createResource($file1)->shouldReturn(false);
    }

    function its_createResource_throws_when_the_path_is_invalid()
    {
        $this->shouldThrow()
            ->duringCreateResource('FooBar');
    }

    function its_hasPath_returns_true_if_the_path_is_watched()
    {
        mfs::mkdir($watched = mfs::$tmpDir.'/watched');

        touch($file1 = $watched.'/foo.txt');
        $this->to($watched);
        $this->hasPath($file1)->shouldReturn(true);
    }

    function its_hasPath_returns_false_if_the_path_is_not_watched()
    {
        mfs::mkdir($watched = mfs::$tmpDir.'/watched');
        mfs::mkdir($unwatched = mfs::$tmpDir.'/unwatched');

        touch($file1 = $watched.'/foo.txt');
        touch($file2 = $unwatched.'/bar.txt');

        $this->to($watched);

        $this->hasPath($file1)->shouldReturn(true);
        $this->hasPath($file2)->shouldReturn(false);
    }

    function its_hasPath_should_check_against_pattern()
    {
        mfs::mkdir($dir = mfs::$tmpDir.'/root/subdir');
        $this->to(mfs::$tmpDir.'/root');

        touch($fphp = $dir.'/foobar.php');
        touch($ftxt = $dir.'/foobar.txt');

        $this->patterns('#.*\.php$#');

        $this->hasPath($fphp)->shouldReturn(true);
        $this->hasPath($ftxt)->shouldReturn(false);

        $this->patterns('#^subdir\/.*\.txt$#');
        $this->hasPath($fphp)->shouldReturn(true);
        $this->hasPath($ftxt)->shouldReturn(true);
    }

    public function it_should_evaluate_file_system_events(
        AdapterInterface $adapter,
        LoggerInterface $logger,
        FilesystemEvent $event
    )
    {
        $adapter->setLogger($logger)
            ->shouldBeCalled();
        $adapter->initialize($this)
            ->shouldBeCalled();
        $adapter->evaluate()
            ->shouldBeCalled();
        $adapter->getChangeSet()
            ->willReturn(array($event))
            ->shouldBeCalled();

        $this->setLogger($logger);
        $this->to(mfs::$tmpDir);
        $this->setAdapter($adapter);
        $this->evaluate();
    }

    function it_should_start_evaluate_filesystem_event_properly(
        AdapterInterface $adapter,
        LoggerInterface $logger,
        FilesystemEvent $event
    )
    {
        $this->callback(function(ChangeSetEvent $event){
            static $count=0;
            $event->getListener()->stop();
            $count++;
        });

        $adapter->setLogger($logger)
            ->shouldBeCalled();
        $adapter->initialize($this)
            ->shouldBeCalled();
        $adapter->evaluate()
            ->shouldBeCalled();
        $adapter->getChangeSet()
            ->willReturn(array($event))
            ->shouldBeCalled();

        $this->setLogger($logger);
        $this->to(mfs::$tmpDir);
        $this->setAdapter($adapter);
        $this->latency(10000);
        $this->start();
        $this->getChangeset()->shouldContain($event);
    }


}