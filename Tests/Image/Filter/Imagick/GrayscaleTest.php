<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Image\Filter\Imagick;

use Thapp\JitImage\Tests\TestHelperTrait;
use Thapp\JitImage\Image\Filter\Imagick\Grayscale;
use Thapp\JitImage\Tests\Image\Filter\GrayscaleFilterTest;
/**
 * @class GrayscaleTest
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class GrayscaleTest extends GrayscaleFilterTest
{
    use TestHelperTrait;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Image\Filter\Imagick\AbstractImagickFilter', $this->newGrayscale());
    }

    protected function newGrayscale()
    {
        return new Grayscale;
    }

    protected function getFilterInterface()
    {
        return 'Thapp\Image\Filter\Imagick\Grayscale';
    }

    protected function setUp()
    {
        $this->skipIfImagick();
    }
}
