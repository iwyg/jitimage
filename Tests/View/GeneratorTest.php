<?php

/*
 * This File is part of the Thapp\JitImage\Tests\View package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\View;

use Thapp\JitImage\View\Generator;

/**
 * @class GeneratorTest
 *
 * @package Thapp\JitImage\Tests\View
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\View\Generator', new Generator($this->mockJmg()));
    }

    /** @test */
    public function itIsExpectedThat()
    {
        $string = '/some/image.jpg';

        $gen = new Generator($jmg = $this->mockJmg());
        $jmg->method('apply')->willReturn($string);

        $this->assertSame($string, $gen->resize(500, 600));
        $this->assertSame($string, $gen->scale(500));
        $this->assertSame($string, $gen->pixel(10000));
        $this->assertSame($string, $gen->fit(400, 400));
        $this->assertSame($string, $gen->cropAndResize(400, 400));
        $this->assertSame($string, $gen->crop(400, 400));
        $this->assertSame($string, $gen->get());

    }

    /** @test */
    public function itShouldgetPath()
    {
        $gen = new Generator($jmg = $this->mockJmg());
        $gen->setPath('foo');
        $this->assertSame('foo', $gen->getPath());
    }

    /** @test */
    public function itShouldgetSource()
    {
        $gen = new Generator($jmg = $this->mockJmg());
        $gen->setSource('image.jpg');
        $this->assertSame('image.jpg', $gen->getSource());
    }

    /** @test */
    public function itShouldBeClonable()
    {
        $gen = new Generator($jmg = $this->mockJmg());
        $gen->setPath('path');
        $gen->setSource('image.jpg');

        $clone = clone $gen;

        $this->assertNull($clone->getPath());
        $this->assertNull($clone->getSource());
    }

    /** @test */
    public function itFilterShouldReturnGenerator()
    {
        $gen = new Generator($jmg = $this->mockJmg());
        $this->assertSame($gen, $gen->filter('gs;c=1'));
    }

    protected function mockJmg()
    {
        return $this->getMockBuilder('Thapp\JitImage\View\Jmg')->disableOriginalConstructor()->getMock();
    }
}
