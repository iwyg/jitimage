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

use Thapp\JitImage\Image\Filter\Rotate;

/**
 * @class ConvertTest
 *
 * @package Thapp\JitImage\Tests\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class RotateTest extends FilterTest
{
    /** @test */
    public function itShouldBeCute()
    {
        $filter = new Rotate;
        $proc = $this->mockProc();
        $proc->expects($this->once())->method('getdriver')->willReturn($image = $this->mockImage());
        $image->expects($this->once())->method('getPalette')->willReturn($palette = $this->mockPalette());
        $palette->expects($this->once())->method('getColor')->with(hexdec('ffffff'))->willReturn($this->mockColor());
        $filter->apply($proc, ['d' => 180, 'c' => '#ffffff']);
    }

}
