<?php

/*
 * This File is part of the Thapp\JitImage\Tests package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests;

use Thapp\JitImage\Parameters;

/**
 * @class ProcessorTest
 *
 * @package Thapp\JitImage\Tests
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Thapp\JitImage\Exception\ProcessorException
     */
    public function itShouldThrowIfNoImageIsLoaded()
    {
        $proc = $this->newProcessor();
        $proc->process(Parameters::fromString('0'));
    }

    /** @test */
    public function itShouldCloseHandleAfterUnload()
    {
        $handle = tmpfile();

        $proc = $this->newProcessor();
        $proc->load($res = $this->mockFileresource());

        $res->expects($this->any())->method('getHandle')->willReturn($handle);

        $proc->close();

        $this->assertFalse(is_resource($handle));
    }

    protected function mockFileResource()
    {
        return $this->getMockBuilder('Thapp\JitImage\Resource\Fileresource')
            ->disableOriginalConstructor()->getMock();
    }

    abstract protected function prepareLoaded();

    abstract protected function mockDriver();

    abstract protected function newProcessor();
}
