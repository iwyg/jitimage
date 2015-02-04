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

use Thapp\JitImage\Image\Filter\Convert;

/**
 * @class ConvertTest
 *
 * @package Thapp\JitImage\Tests\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ConvertTest extends FilterTest
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowOnInvalidOption()
    {
        $filter = new Convert;
        $filter->apply($this->mockProc(), ['ftm' => 'bar']);
    }

    /** @test */
    public function itShouldParseLongopts()
    {
        $filter = new Convert;
        $proc = $this->mockProc();
        $proc->expects($this->once())->method('getdriver')->willReturn($this->mockImage());
        $filter->apply($proc, ['format' => 'jpeg']);
    }

    /** @test */
    public function itShouldSupportDriver()
    {
        $filter = new Convert;
        $proc = $this->mockProc();
        $proc->expects($this->once())->method('getdriver')->willReturn($this->mockImage());
        $filter->supports($proc);
    }
}
