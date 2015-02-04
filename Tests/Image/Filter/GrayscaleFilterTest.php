<?php

/*
 * This File is part of the Thapp\JitImage package
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
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class GrayscaleFilterTest extends FilterTest
{
    /** @test */
    abstract public function itShouldBeInstantiable();

    /** @test */
    public function itShouldReceiveCorrectOptions()
    {
        $proc = $this->mockProc();
        $proc->expects($this->once())->method('getDriver')->willReturn($image = $this->mockImage());
        $image->expects($this->once())->method('filter')->will($this->returnCallback(function ($filter) {
            $this->assertInstanceof($this->getFilterInterface(), $filter);
        }));

        $filter = $this->newGrayscale();
        $filter->apply($proc);
    }

    abstract protected function newGrayscale();
    abstract protected function getFilterInterface();
}
