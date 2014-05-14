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

use Psr\Log\LogLevel as BaseLogLevel;

/**
 * Class LogLevel
 *
 */
class LogLevel extends BaseLogLevel
{
    const LISTEN_DEBUG = 'listen.debug';
}