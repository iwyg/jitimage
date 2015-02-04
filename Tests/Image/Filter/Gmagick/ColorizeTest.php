<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Image\Filter\Gmagick;

use Thapp\JitImage\Tests\TestHelperTrait;
use Thapp\JitImage\Image\Filter\Gmagick\Colorize;
use Thapp\JitImage\Tests\Image\Filter\ColorizeFilterTest;

/**
 * @class ColorizeTest
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ColorizeTest extends ColorizeFilterTest
{
    use TestHelperTrait;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Image\Filter\Gmagick\AbstractGmagickFilter', $this->newColorize());
    }
    protected function newColorize()
    {
        return new Colorize;
    }

    protected function mockImage()
    {
        return $this->getMockBuilder('Thapp\Image\Driver\Gmagick\Image')
            ->disableOriginalConstructor()->getMock();
    }

    protected function setUp()
    {
        $this->skipIfGmagick();
    }
}
