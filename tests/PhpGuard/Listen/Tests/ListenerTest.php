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


use PhpGuard\Listen\Event\ChangeSetEvent;
use PhpGuard\Listen\Listener;
use Symfony\Component\Finder\SplFileInfo;

class ListenerTest extends TestCase
{
    private $isCallbackCalled = false;

    /**
     * @param $pattern
     * @param $path
     * @param bool $expected
     * @dataProvider getShouldOnlyMatchPattern
     */
    public function testShouldOnlyMatchPattern($pattern,$path,$expected=true)
    {
        $dirname = dirname($path);

        $this->mkdir($dirname);
        touch($path);

        $listener = new Listener(self::$tmpDir);

        //$spl = new SplFileInfo($file,$subPath,$subPathName);
        $listener->patterns($pattern);

        $retVal = $listener->hasPath($path);
        if($expected){
            $this->assertTrue($retVal);
        }else{
            $this->assertFalse($retVal);
        }
    }

    public function getShouldOnlyMatchPattern()
    {
        parent::setUpBeforeClass();
        $dir = self::$tmpDir;

        return array(
            array('#.*\.php$#',$dir.'/foo/bar/test.php'),
            array('#.*\.php$#',$dir.'/foo/bar/test.php'),
            array('#.*\.txt$#',$dir.'/foo/bar/test.php',false),
            array('#.*\.txt$#',$dir.'/foo/bar',true),
            array('#.*$#','/tmp/foobar.txt',false)
        );
    }

    public function testShouldListenFilesystemEvent()
    {
        $this->isCallbackCalled = false;
        self::cleanDir($dir = self::$tmpDir);
        self::mkdir($dir);

        $listener = new Listener();
        $listener
            ->to($dir)
            ->latency(0.01)
            ->callback(array($this,'listenerCallback'))
        ;

        $listener->alwaysNotify(true);
        $listener->start();

        touch($file = $dir.'/foo.txt');
        file_put_contents($file,'HELLO WORLD AJA YA');

        $this->assertTrue($this->isCallbackCalled);
    }

    public function listenerCallback(ChangeSetEvent $event)
    {
        $event->getListener()->stop();
        $this->isCallbackCalled = true;
    }
}