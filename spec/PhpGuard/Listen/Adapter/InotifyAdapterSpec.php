<?php

namespace spec\PhpGuard\Listen\Adapter;

use PhpGuard\Listen\Adapter\InotifyAdapter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InotifyAdapterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\InotifyAdapter');
    }

    function it_should_evaluate_filesystem_event()
    {

    }
}