<?php

namespace spec\PhpGuard\Listen\Adapter\Pooling;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WatchMapSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Adapter\Pooling\WatchMap');
    }
}