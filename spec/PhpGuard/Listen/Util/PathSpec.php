<?php

namespace spec\PhpGuard\Listen\Util;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Util\Path');
    }

    function it_should_generate_the_symfony_finder_SplFileInfo_from_base_dir()
    {
        $subPathName = str_replace(getcwd().'/','',__FILE__);
        $subPath = dirname($subPathName);

        $info = $this->createSplFileInfo('/foo/bar',getcwd());
        $info->getRealPath()->shouldReturn(getcwd());
        $info->getRelativePath()->shouldReturn('');
        $info->getRelativePathname()->shouldReturn('');

        $info = $this->createSplFileInfo(getcwd(),__FILE__);
        $info->getRealPath()->shouldReturn(__FILE__);
        $info->getRelativePath()->shouldReturn($subPath);
        $info->getRelativePathname()->shouldReturn($subPathName);
    }
}