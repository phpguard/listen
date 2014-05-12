<?php

namespace spec\PhpGuard\Listen\Resource;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileResourceSpec extends ObjectBehavior
{
    protected $file;

    function let()
    {
        $this->file = sys_get_temp_dir().'/phpguard-listen-test.txt';
        touch($this->file);
        $this->beConstructedWith($this->file);
    }

    function letgo()
    {
        if(is_file($this->file)){
            unlink($this->file);
        }
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Resource\FileResource');
    }

    function it_should_implement_the_ResourceInterface()
    {
        $this->shouldImplement('PhpGuard\Listen\Resource\ResourceInterface');
    }

    function it_getID_should_generated_automatically()
    {
        $this->getID()->shouldReturn(md5('f'.$this->file));
    }

    function it_isExists_returns_true_if_the_resource_exists()
    {
        $this->shouldBeExists();
    }

    function it_isExists_returns_false_if_the_resource_does_not_exists()
    {
        $this->shouldBeExists();
        @unlink($this->file);
        $this->shouldNotBeExists();
    }

    function it_isFresh_returns_true_if_the_resource_has_not_changed()
    {
        $this->shouldBeFresh(time()+10);
    }

    function it_isFresh_returns_false_if_the_resource_has_been_updated()
    {
        $this->shouldNotBeFresh(time()-86400);
    }

    function it_isFresh_returns_false_if_the_resource_does_not_exist()
    {
        $this->beConstructedWith('/foo/bar');
        $this->shouldNotBeFresh(time());
    }
}