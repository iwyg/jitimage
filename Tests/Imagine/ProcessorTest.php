<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Imagine package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Imagine;

use Thapp\JitImage\Imagine\Processor;

/**
 * @class ProcessorTest
 *
 * @package Thapp\JitImage\Tests\Imagine
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\ProcessorInterface', new Processor($this->mockImagine()));
    }

    protected function mockImagine()
    {
        return $this->getMock('Imagine\Image\ImagineInterface');
    }
}
