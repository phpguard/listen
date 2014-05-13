<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PhpGuard\Listen;

MockFileSystem::$tmpDir = sys_get_temp_dir().'/phpguard-test';

class MockFileSystem
{
    static public $tmpDir;
    static public function mkdir($dir)
    {
        @mkdir($dir,0755,true);
    }

    static public function cleanDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $flags = \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveDirectoryIterator($dir, $flags);
        $iterator = new \RecursiveIteratorIterator(
            $iterator, \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                rmdir((string) $path);
            } else {
                unlink((string) $path);
            }
        }

        rmdir($dir);
    }
}

