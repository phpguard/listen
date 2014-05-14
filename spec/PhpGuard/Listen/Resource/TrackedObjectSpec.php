<?php

namespace spec\PhpGuard\Listen\Resource;

use PhpGuard\Listen\Resource\ResourceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TrackedObjectSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Resource\TrackedObject');
    }

    function its_id_should_be_mutable()
    {
        $this->setID('any')->shouldReturn($this);
        $this->getID()->shouldReturn('any');
    }

    function its_resource_should_be_mutable(ResourceInterface $resource)
    {
        $this->setResource($resource);
        $this->getResource()->shouldReturn($resource);
    }

    function its_checksum_should_be_mutable()
    {
        $this->setChecksum('any')->shouldReturn($this);
        $this->getChecksum()->shouldReturn('any');
    }
}