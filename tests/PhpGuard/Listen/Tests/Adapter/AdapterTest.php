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
        $this->assertCount(1,$events);
        $this->assertEventHasResource($file1,FilesystemEvent::DELETE,$events);

        $adapter->evaluate();
        $events = $listener->getChangeSet();
        $this->assertCount(0,$events);
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
 