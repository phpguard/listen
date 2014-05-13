<?php

namespace spec\PhpGuard\Listen\Resource;

use PhpGuard\Listen\Resource\ResourceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TrackedResourceSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Resource\TrackedResource');
    }

    function its_id_should_be_mutable()
    {
        $this->setID('any')->shouldReturn($this);
        $this->getID()->shouldReturn('any');
    }

    function its_OriginalResource_should_be_mutable(ResourceInterface $resource)
    {
        $this->setOriginalResource($resource);
        $this->getOriginalResource()->shouldReturn($resource);
    }

    function its_listeners_should_be_the_SplObjecStorage_object()
    {
        $this->getListeners()->shouldHaveType('SplObjectStorage');
    }

    function its_checksum_should_be_mutable()
    {
        $this->setChecksum('any')->shouldReturn($this);
        $this->getChecksum()->shouldReturn('any');
    }
}