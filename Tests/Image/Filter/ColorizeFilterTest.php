<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Image\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Image\Filter;

/**
 * @class ColorizeFilterTest
 *
 * @package Thapp\JitImage\Tests\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class ColorizeFilterTest extends FilterTest
{
    /** @test */
    abstract public function itShouldBeInstantiable();

    /** @test */
    public function itShouldReceiveCorrectOptions()
    {
        $proc = $this->mockProc();
        $proc->expects($this->once())->method('getDriver')->willReturn($image = $this->mockImage());
        $image->expects($this->once())->method('getPalette')->willReturn($palette = $this->mockPalette());
        $palette->expects($this->once())->method('getColor')->willReturn($this->mockColor());
        $image->expects($this->once())->method('filter');

        $filter = $this->newColorize();
        $filter->apply($proc, ['c' => '#ff0000']);
    }

    /** @test */
    public function itShouldSupportDriver()
    {
        $filter = $this->newColorize();
        $proc = $this->mockProc();
        $proc->expects($this->once())->method('getDriver')->willReturn($this->mockImage());

        $this->assertTrue($filter->supports($proc));
    }

    abstract protected function newColorize();
}
