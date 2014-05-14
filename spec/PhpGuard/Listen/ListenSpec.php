<?php

namespace spec\PhpGuard\Listen;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listen;
use PhpGuard\Listen\Listener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class ListenSpec extends ObjectBehavior
{
    function it_should_create_new_listener()
    {
        $this->to(__DIR__)->shouldHaveType('PhpGuard\\Listen\\Listener');
    }

    function it_should_choose_best_adapter_to_use()
    {
        if(function_exists('inotify_init')){
            $this->getDefaultAdapter()->shouldHaveType('PhpGuard\\Listen\\Adapter\\InotifyAdapter');
        }else{
            $this->getDefaultAdapter()->shouldHaveType('PhpGaurd\\Listen\\Adapter\\BasicAdapter');
        }
    }


}