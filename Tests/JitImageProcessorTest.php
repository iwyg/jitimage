<?php

/**
 * This File is part of the Thapp\JitImage\Tests package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests;

use \Mockery as m;
use \Thapp\JitImage\JitImageProcessor;

/**
 * @class JitImageProcessorTest
 * @package Thapp\JitImage\Tests
 * @version $Id$
 */
class JitImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldNotImmedialtelySetQaulity()
    {
        $fails = false;

        $processor = new JitImageProcessor($d = m::mock('\Thapp\Image\Driver\DriverInterface'));

        $d->shouldReceive('setQuality')->andReturnUsing(function () use (&$fails) {
            $fails = true;
        });

        $processor->setQuality(80);

        $this->assertFalse($fails);

        $fails = true;

        // now do load
        $processor = new JitImageProcessor($d = m::mock('\Thapp\Image\Driver\DriverInterface'));

        $d->shouldReceive('load');
        $d->shouldReceive('setQuality')->andReturnUsing(function () use (&$fails) {
            $fails = false;
        });

        $processor->setQuality(80);
        $processor->load('source');

        $this->assertFalse($fails);
    }
}
