<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Resource;

use Symfony\Component\Finder\SplFileInfo as BaseSplFileInfo;

/**
 * Class SplFileInfo
 *
 */
class SplFileInfo extends BaseSplFileInfo
{
    static public function createFromBaseDir($baseDir,$path)
    {
        $absolutePath = realpath($path);
        $baseDirLength = strlen($baseDir);

        if($baseDir === substr($absolutePath,0,$baseDirLength)){
            $subPathName = ltrim(substr($absolutePath,$baseDirLength),'/\\');
            $dir = dirname($subPathName);
            $subPath = '.' === $dir ? '':$dir;
        }else{
            $subPath = $subPathName = '';
        }
        return new self($absolutePath,$subPath,$subPathName);
    }
}