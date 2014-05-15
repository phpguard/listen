<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Tests\Adapter;

use PhpGuard\Listen\Adapter\AdapterInterface;
use PhpGuard\Listen\Event\ChangeSetEvent;
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Tests\TestCase;

class TestedListener extends Listener
{
    public function start()
    {
        $this->latency(1000);
        $this->alwaysNotify(true);
        $this->callback(function(ChangeSetEvent $event){
            // always stop after each start
            $event->getListener()->stop();
        });
        parent::start();
    }
}


abstract class AdapterTest extends TestCase
{
    public function setUp()
    {
        if(is_dir(self::$tmpDir)){
            $this->cleanDir(self::$tmpDir);
        }

        $this->mkdir(self::$tmpDir);

    }

    public function tearDown()
    {
        $this->cleanDir(self::$tmpDir);
    }

    /**
     * @return AdapterInterface
     */
    abstract public function getAdapter();

    protected function getMinimumInterval()
    {
        return 0;
    }

    protected function sleep()
    {
        usleep($this->getMinimumInterval());
    }

    public function testShouldMonitorBasicFileEvent()
    {

        $tmp = self::$tmpDir;
        touch($file1 = $tmp.'/file1.txt');
        touch($file2 = $tmp.'/file2.txt');

        $listener = new TestedListener($tmp);
        $listener->setAdapter($this->getAdapter());

        file_put_contents($file1,'Hello World',LOCK_EX);
        file_put_contents($file2,'Foo Bar',LOCK_EX);
        touch($file3 = $tmp.'/file3.txt');

        $listener->start();
        $changeSet = $listener->getChangeSet();

        $this->assertCount(3,$changeSet);

        $this->assertEventHasResource($file1,FilesystemEvent::MODIFY,$changeSet);
        $this->assertEventHasResource($file2,FilesystemEvent::MODIFY,$changeSet);
        $this->assertEventHasResource($file3,FilesystemEvent::CREATE,$changeSet);

        unlink($file1);

        $listener->start();
        $changeSet = $listener->getChangeSet();
        $this->assertCount(1,$changeSet);
        $this->assertEventHasResource($file1,FilesystemEvent::DELETE,$changeSet);

        $listener->start();
        $changeSet = $listener->getChangeSet();
        $this->assertCount(0,$changeSet);
    }

    public function testShouldMonitorBasicDirectoryEvent()
    {
        $tmp = self::$tmpDir;

        $listener = new TestedListener($tmp);
        $listener->setAdapter($this->getAdapter());

        $this->mkdir($dir1 = $tmp.'/dir');
        $this->mkdir($l1 = $dir1.'/l1');

        touch($f1 = $dir1.'/dir1.txt');
        touch($f2 = $l1.'/l1.txt');

        $listener->start();
        $changeset = $listener->getChangeSet();

        $this->assertCount(2,$changeset);
        $this->assertEventHasResource($f1,FilesystemEvent::CREATE,$changeset);
        $this->assertEventHasResource($f2,FilesystemEvent::CREATE,$changeset);

        $this->mkdir($l2 = $l1.'/l2');
        touch($f3 = $l2.'/l2.txt');

        $listener->start();
        $changeset = $listener->getChangeSet();
        $this->assertCount(1,$changeset);
        $this->assertEventHasResource($f3,FilesystemEvent::CREATE,$changeset);

        unlink($f3);
        rmdir($l2);

        $listener->start();
        $changeset = $listener->getChangeSet();

        $this->assertCount(1,$changeset);
        $this->assertEventHasResource($f3,FilesystemEvent::DELETE,$changeset);

        $listener->start();
        $this->assertCount(0,$listener->getChangeSet());
    }

    public function testShouldMonitorRecursiveDelete()
    {
        $dir = self::$tmpDir;
        $this->mkdir($l1 = $dir.'/l1');
        $this->mkdir($l2 = $l1.'/l2');
        $this->mkdir($l3 = $l2.'/l3');

        touch($fl1 = $l1.'/l1.txt');
        touch($fl2 = $l2.'/l2.txt');
        touch($fl3 = $l3.'/l3.txt');

        $listener = new TestedListener($dir);
        $listener->setAdapter($this->getAdapter());

        //$this->sleep();
        $this->cleanDir($dir);
        $listener->start();
        $changeSet = $listener->getChangeSet();
        $this->assertCount(3,$changeSet);
        $this->assertEventHasResource($fl1,FilesystemEvent::DELETE,$changeSet);
        $this->assertEventHasResource($fl2,FilesystemEvent::DELETE,$changeSet);
        $this->assertEventHasResource($fl3,FilesystemEvent::DELETE,$changeSet);
    }

    public function testShouldOnlyTrackFilteredPattern()
    {
        $dir = self::$tmpDir;
        $this->mkdir($foo        = $dir.'/foo');
        $this->mkdir($fooBar     = $dir.'/foo/bar');
        $this->mkdir($hello      = $dir.'/hello');
        $this->mkdir($helloWorld = $dir.'/hello/world');

        $global = new TestedListener($dir);
        $global->setAdapter($this->getAdapter())
            ->patterns('#.*\.txt$#')
        ;

        $php1 = new TestedListener($dir);
        $php1->setAdapter($this->getAdapter())
            ->patterns('#^foo\/.*\.php$#')
            ->patterns('#^bar\/.*\.php$#')
            ->patterns('#^hello\/.*\.php$#')
            ->patterns('#^world\/.*\.php$#')
        ;

        $php2 = new TestedListener($dir);
        $php2->setAdapter($this->getAdapter())
            ->patterns('#^foo\/.*\.php$#')
        ;

        touch($f1 = $foo.'/foo.txt');
        touch($f2 = $fooBar.'/bar.txt');
        touch($f3 = $hello.'/hello.txt');
        touch($f4 = $helloWorld.'/world.txt');

        touch($fphp1 = $foo.'/foo.php');
        touch($fphp2 = $fooBar.'/bar.php');
        touch($fphp3 = $hello.'/hello.php');
        touch($fphp4 = $helloWorld.'/world.php');

        // should not match this file
        touch($try1 = $dir.'/test.php');

        $global->start();

        // should match all file
        $changeSet = $global->getChangeSet();
        $this->assertCount(4,$changeSet);
        $this->assertEventHasResource($f1,FilesystemEvent::CREATE,$global->getChangeSet());
        $this->assertEventHasResource($f2,FilesystemEvent::CREATE,$global->getChangeSet());
        $this->assertEventHasResource($f3,FilesystemEvent::CREATE,$global->getChangeSet());
        $this->assertEventHasResource($f4,FilesystemEvent::CREATE,$global->getChangeSet());

        // should match all php file
        $php1->start();
        $changeSet = $php1->getChangeSet();
        $this->assertCount(4,$changeSet);
        $this->assertEventHasResource($fphp1,FilesystemEvent::CREATE,$changeSet);
        $this->assertEventHasResource($fphp2,FilesystemEvent::CREATE,$changeSet);
        $this->assertEventHasResource($fphp3,FilesystemEvent::CREATE,$changeSet);
        $this->assertEventHasResource($fphp4,FilesystemEvent::CREATE,$changeSet);

        // should match only foo/*.php
        $php2->start();
        $changeSet = $php2->getChangeSet();
        $this->assertCount(2,$changeSet);
        $this->assertEventHasResource($fphp1,FilesystemEvent::CREATE,$changeSet);
        $this->assertEventHasResource($fphp2,FilesystemEvent::CREATE,$changeSet);
    }

    public function testShouldNotTrackIgnoredDir()
    {
        $dir = self::$tmpDir;
        $this->mkdir($foo        = $dir.'/foo');
        $this->mkdir($fooBar     = $dir.'/foo/bar');
        $this->mkdir($hello      = $dir.'/hello');
        $this->mkdir($helloWorld = $dir.'/hello/world');

        $listener1 = new TestedListener($dir);
        $listener1->ignores('#foo/bar.*$#');
        $listener1->setAdapter($this->getAdapter());

        touch($f1 = $foo.'/foo.txt');
        touch($f2 = $fooBar.'/bar.txt');
        touch($f3 = $hello.'/hello.txt');
        touch($f4 = $helloWorld.'/world.txt');

        $listener1->start();
        $changeSet = $listener1->getChangeSet();

        $this->assertCount(3,$changeSet);
        $this->assertEventHasResource($f1,FilesystemEvent::CREATE,$changeSet);
        $this->assertEventHasResource($f3,FilesystemEvent::CREATE,$changeSet);
        $this->assertEventHasResource($f4,FilesystemEvent::CREATE,$changeSet);
    }

    protected function assertEventHasResource($resource,$type,$changeSet)
    {
        $result = array();
        foreach ($changeSet as $event) {
            if ($resource === (string) $event->getResource()) {
                $result[] = $event->getType();
            }
        }

        $types = array(
            1 => 'CREATE',
            2 => 'MODIFY',
            4 => 'DELETE',
        );


        if ($result) {
            return $this->assertTrue(in_array($type, $result), sprintf('Expected event: %s, actual: %s ', $types[$type], implode(' or ', array_intersect_key($types, array_flip($result)))));
        }

        $this->fail(sprintf('Can not find "%s" %s event', $resource,$types[$type]));
    }
    
}