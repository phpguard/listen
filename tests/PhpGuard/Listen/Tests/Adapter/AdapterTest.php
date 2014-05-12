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
use PhpGuard\Listen\Event\FilesystemEvent;
use PhpGuard\Listen\Listen;
use PhpGuard\Listen\Listener;
use PhpGuard\Listen\Tests\TestCase;

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
        $adapter = $this->getAdapter();

        $tmp = self::$tmpDir;
        touch($file1 = $tmp.'/file1.txt');
        touch($file2 = $tmp.'/file2.txt');

        $listener = new Listener($tmp);
        $adapter->initialize($listener);

        file_put_contents($file1,'Hello World',LOCK_EX);
        file_put_contents($file2,'Foo Bar',LOCK_EX);
        touch($file3 = $tmp.'/file3.txt');

        $adapter->evaluate();
        $events = $listener->getChangeSet();

        $this->assertCount(3,$events);
        $this->assertEventHasResource($file1,FilesystemEvent::MODIFY,$events);
        $this->assertEventHasResource($file2,FilesystemEvent::MODIFY,$events);
        $this->assertEventHasResource($file3,FilesystemEvent::CREATE,$events);

        unlink($file1);
        $adapter->evaluate();
        $events = $listener->getChangeSet();
        //print_r($events);
        $this->assertCount(1,$events);
        $this->assertEventHasResource($file1,FilesystemEvent::DELETE,$events);

        $adapter->evaluate();
        $events = $listener->getChangeSet();
        $this->assertCount(0,$events);
    }

    public function testShouldMonitorBasicDirectoryEvent()
    {
        $tmp = self::$tmpDir;

        $listener = new Listener($tmp);
        $adapter = $this->getAdapter();
        $adapter->initialize($listener);

        $this->mkdir($dir1 = $tmp.'/dir');
        $this->mkdir($l1 = $dir1.'/l1');

        touch($f1 = $dir1.'/dir1.txt');
        touch($f2 = $l1.'/l1.txt');
        $adapter->evaluate();
        $changeset = $listener->getChangeSet();

        $this->assertCount(2,$changeset);
        $this->assertEventHasResource($f1,FilesystemEvent::CREATE,$changeset);
        $this->assertEventHasResource($f2,FilesystemEvent::CREATE,$changeset);

        $this->mkdir($l2 = $l1.'/l2');
        touch($f3 = $l2.'/l2.txt');
        $adapter->evaluate();
        $changeset = $listener->getChangeSet();
        $this->assertCount(1,$changeset);
        $this->assertEventHasResource($f3,FilesystemEvent::CREATE,$changeset);

        unlink($f3);
        rmdir($l2);

        $adapter->evaluate();
        $changeset = $listener->getChangeSet();
        $this->assertCount(1,$changeset);
        $this->assertEventHasResource($f3,FilesystemEvent::DELETE,$changeset);

        $adapter->evaluate();
        $this->assertCount(0,$listener->getChangeSet());
    }

    /**
     * @group bug
     */
    public function testShouldMonitorRecursiveDelete()
    {
        $dir = self::$tmpDir;
        $this->mkdir($l1 = $dir.'/l1');
        $this->mkdir($l2 = $l1.'/l2');
        $this->mkdir($l3 = $l2.'/l3');

        touch($fl1 = $l1.'/l1.txt');
        touch($fl2 = $l2.'/l2.txt');
        touch($fl3 = $l3.'/l3.txt');

        $listener = new Listener($dir);
        $adapter = $this->getAdapter();
        $adapter->initialize($listener);

        //file_put_contents($fl2,"HELLO WORLD",LOCK_EX);

        //$this->sleep();
        $this->cleanDir($dir);
        $adapter->evaluate();
        $changeSet = $listener->getChangeSet();
        $this->assertCount(3,$changeSet);
        $this->assertEventHasResource($fl1,FilesystemEvent::DELETE,$changeSet);
        $this->assertEventHasResource($fl2,FilesystemEvent::DELETE,$changeSet);
        $this->assertEventHasResource($fl3,FilesystemEvent::DELETE,$changeSet);
    }

    public function assertEventHasResource($resource,$type,$events)
    {
        $result = array();
        foreach ($events as $event) {
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

        $this->fail(sprintf('Can not find "%s" change event', $resource));
    }
}
 