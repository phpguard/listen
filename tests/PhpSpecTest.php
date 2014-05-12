<?php

/*
 * This file is part of the PhpGuard Listen project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpSpec\Console\Application;
use Symfony\Component\Console\Input\StringInput;

class PhpSpecTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     * @group phpspec
     */
    public function testShouldPassPhpSpecTest()
    {
        try{
            $app = new Application('phpguard-listen-spec');
            $app->setAutoExit(false);
            $app->setCatchExceptions(true);
            $input = new StringInput('run --ansi');
            $app->run($input);
            $this->assertTrue(true);
        }catch(\Exception $e){
            throw $e;
        }
    }
}
 