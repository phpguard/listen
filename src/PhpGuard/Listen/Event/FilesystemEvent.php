<?php

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpGuard\Listen\Event;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Class FilesystemEvent
 *
 */
class FilesystemEvent
{
    const CREATE    = 1;
    const MODIFY    = 2;
    const DELETE    = 4;
    const ALL       = 7;

    /**
     * @var int
     */
    private $type;

    /**
     * @var \Symfony\Component\Finder\SplFileInfo
     */
    private $resource;

    protected static $types = array(
        1 => 'create',
        2 => 'modify',
        4 => 'delete',
    );

    /**
     * @param SplFileInfo $resource
     * @param int $type
     * @throws \InvalidArgumentException If type is an invalid constant
     */
    public function __construct($resource,$type)
    {
        if(!isset(self::$types[$type])){
            throw new \InvalidArgumentException('Invalid file system event type');
        }
        $this->type = $type;
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getHumanType()
    {
        return self::$types[$this->type];
    }

    /**
     * @return SplFileInfo
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isCreate()
    {
        return self::CREATE === $this->type;
    }

    public function isModify()
    {
        return self::MODIFY === $this->type;
    }

    public function isDelete()
    {
        return $this->getType() === self::DELETE;
    }
}
