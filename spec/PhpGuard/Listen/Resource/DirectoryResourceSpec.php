<?php

namespace spec\PhpGuard\Listen\Resource;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DirectoryResourceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(getcwd());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Resource\DirectoryResource');
    }

    function it_should_implement_the_ResourceInterface()
    {
        $this->shouldImplement('PhpGuard\Listen\Resource\ResourceInterface');
    }

    function its_getID_generated_by_md5_directory_name()
    {
        $this->beConstructedWith(getcwd());
        $this->getID()->shouldReturn(md5('d'.getcwd()));
    }

    function it_isExists_returns_true_if_the_resource_exists()
    {
        $this->shouldBeExists();
    }

    function it_isExists_returns_false_if_the_resource_does_not_exists()
    {
        $this->beConstructedWith('/foo/bar');
        $this->shouldNotBeExists();
    }

    function it_getModificationTime_returns_minus_one_if_the_resource_does_not_exists()
    {
        $this->beConstructedWith('/foo/bar');
        $this->getModificationTime()->shouldReturn(-1);
    }

    function it_isFresh_returns_true_if_the_resource_has_not_changed()
    {
        $this->shouldBeFresh(time() + 10);
    }

    function it_isFresh_returns_false_if_the_resource_has_been_updated()
    {
        $this->shouldNotBeFresh(time()-86400);
    }

    function it_isFresh_returns_false_if_the_resource_does_not_exist()
    {
        $this->beConstructedWith('/foo/bar');
        $this->shouldNotBeFresh(time()+10);
    }

    function it_getResource_returns_the_resource_file_name()
    {
        $this->getResource()->shouldReturn(getcwd());
    }
}