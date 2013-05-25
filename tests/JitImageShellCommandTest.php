<?php

/**
 * This File is part of the tests package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Tests\JitImage;

use Thapp\Tests\JitImage\Stubs\ShellCommandStub;

/**
 * Class: JitImageShellCommandTest
 *
 * @uses TestCase
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageShellCommandTest extends TestCase
{
    protected $cmd;

    public function setUp()
    {
        $this->cmd = new ShellCommandStub;
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function testCommandShouleFail()
    {
        $this->cmd->runCmd('somecrap');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testCommandShouleFailWithGivenException()
    {
        $this->cmd->runCmd('somecrap', 'InvalidArgumentException');
    }

    /**
     * @test
     */
    public function testCommandShouldExecuteCallbackAfterFailure()
    {
        try {
            $this->cmd->runCmd('somecrap', 'InvalidArgumentException', function ($stderr) {
                $this->assertTrue(true);
            });
        } catch(\Exception $e) {}
    }

    /**
     * @test
     */
    public function testEscapeCommandShouldIgnoreNoEscpaeChars()
    {
        $cmd = 'somecrap with args #';
        try {
         $this->cmd->runCmd($cmd, 'InvalidArgumentException', null, ['#']);
        } catch(\Exception $e) {}

        $this->assertEquals($cmd, $this->cmd->getLastCmd());
    }

    /**
     * @test
     */
    public function testEscapeCommandShouldEscapeNoEscpaeChars()
    {
        $cmd = 'somecrap with args #';
        try {
         $this->cmd->runCmd($cmd, 'InvalidArgumentException', null);
        } catch(\Exception $e) {}

        $this->assertTrue($cmd !== $this->cmd->getLastCmd());
    }

    /**
     * @test
     */
    public function testShouldReturnStdout()
    {
        $stdout = $this->cmd->runCmd('ls -l');
        $this->assertTrue(strlen($stdout) > 0);
    }
}
