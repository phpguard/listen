<?php

namespace spec\PhpGuard\Listen\Util;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathUtilSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PhpGuard\Listen\Util\PathUtil');
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

    function it_should_generate_path_id_from_dirname_or_filename()
    {
        $this->createPathID(__DIR__)
            ->shouldReturn(md5('d'.__DIR__));
        $this->createPathID(__FILE__)
            ->shouldReturn(md5('f'.__FILE__));

        $this->createPathID('any')
            ->shouldReturn(md5('any'));
    }
}