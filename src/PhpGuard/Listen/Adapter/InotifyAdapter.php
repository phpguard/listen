<?php

namespace PhpGuard\Listen\Adapter;

/*
 * This file is part of the PhpGuard project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class InotifyAdapter
 *
 */
class InotifyAdapter extends BasicAdapter
{
    private $inotify;

    private $inotifyEventMask;

    public function __construct()
    {
        $this->inotifyEventMask = IN_ATTRIB;
        $this->inotify = inotify_init();
        stream_set_blocking($this->inotify,0);
    }

    public function evaluate()
    {

    }
}