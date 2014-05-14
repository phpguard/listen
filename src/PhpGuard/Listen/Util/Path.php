<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Util;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Path
 *
 */
class Path
{

    /**
     * Generate SplFileInfo from base dir
     *
     * @param   string $baseDir Base dir for path
     * @param   string  $path       A file/directory name
     * @return  SplFileInfo
     */
    public function createSplFileInfo($baseDir, $path)
    {
        $absPath = realpath($path);
        $baseDirLen = strlen($baseDir);

        if($baseDir === substr($absPath,0,$baseDirLen)){
            $subPathName = ltrim(substr($absPath,$baseDirLen),'\\/');
            $dir = dirname($subPathName);
            $subPath = '.' === $dir ? '':$dir;
        }else{
            $subPath = $subPathName = '';
        }

        return new SplFileInfo($absPath,$subPath,$subPathName);
    }
}
