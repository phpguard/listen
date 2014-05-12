<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Tests;


use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Resource\SplFileInfo;

class ListenerTest extends TestCase
{
    /**
     * @param $pattern
     * @param $path
     * @param bool $expected
     * @dataProvider getShouldOnlyMatchPattern
     */
    public function testShouldOnlyMatchPattern($pattern,$path,$expected=true)
    {
        $file = self::$tmpDir.'/'.$path;
        $dirname = dirname($file);
        $this->mkdir($dirname);
        touch($file);

        $listener = new Listener(self::$tmpDir);

        $spl = SplFileInfo::createFromBaseDir(self::$tmpDir,$file);
        $listener->patterns($pattern);

        $retVal = $listener->hasPath($spl);
        if($expected){
            $this->assertTrue($retVal);
        }else{
            $this->assertFalse($retVal);
        }
    }

    public function getShouldOnlyMatchPattern()
    {
        return array(
            array('#.*\.php$#','foo/bar/test.php'),
            array('#.*\.php$#',self::$tmpDir.'/foo/bar/test.php'),
            array('#.*\.txt$#','foo/bar/test.php',false),
            array('#.*\.txt$#','foo/bar',true)
        );
    }
}