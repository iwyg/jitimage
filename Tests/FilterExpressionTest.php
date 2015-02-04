<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */


namespace Thapp\JitImage\Tests;

use Thapp\JitImage\FilterExpression;

/**
 * @class FilterExpressionTest
 *
 * @package Thapp\Image
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class FilterExpressionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itIShouldCompileExpression()
    {
        $params = ['circle' => ['o' => 1, 'n' => 2], 'gscale', 'lucid' => ['c' => 1.4]];
        $expr = new FilterExpression($params);

        $this->assertEquals('circle;o=1;n=2:gscale:lucid;c=1.4', $expr->compile());
    }

    /** @test */
    public function toStringShouldCallCompile()
    {
        $params = ['circle' => ['o' => 1, 'n' => 2], 'gscale', 'lucid' => ['c' => 1.4]];
        $expr = new FilterExpression($params);
        $this->assertEquals('circle;o=1;n=2:gscale:lucid;c=1.4', (string)$expr);
    }

    /** @test */
    public function itShouldStripPrefix()
    {
        $str = 'prefix:lucid;g=2';
        $expr = new FilterExpression($str, 'prefix');
        $this->assertEquals('lucid;g=2', (string)$expr);
    }

    /** @test */
    public function itShouldAddFilter()
    {
        $str = 'lucid;g=2';
        $expr = new FilterExpression($str);
        $expr->addFilter('foo', ['bar' => 100]);
        $this->assertEquals('lucid;g=2:foo;bar=100', (string)$expr);
    }

    /** @test */
    public function itShouldIgnoreEmptyFilterName()
    {
        $str = 'lucid;g=2';
        $expr = new FilterExpression($str);
        $expr->addFilter('', ['bar' => 100]);
        $this->assertEquals($str, (string)$expr);
    }

    /** @test */
    public function itShouldReturnCompiledStingIfCompiled()
    {
        $str = 'lucid;g=2';
        $expr = new FilterExpression($str);
        $this->assertEquals($str, $expr->compile());
        $this->assertEquals($str, (string)$expr);
    }

    /** @test */
    public function itShouldReturnEmptyArray()
    {
        $expr = new FilterExpression('');
        $this->assertEquals([], $expr->all());
    }

    /** @test */
    public function itShouldNullEmptyOptionValues()
    {
        $str = 'lucid;g=';
        $expr = new FilterExpression($str);
        $this->assertEquals(['lucid' => ['g' => null]], $expr->all());
    }

    /** @test */
    public function itShouldFloatValFloats()
    {
        $str = 'lucid;g=1.1';
        $expr = new FilterExpression($str);
        $this->assertEquals(['lucid' => ['g' => 1.1]], $expr->all());
    }

    /** @test */
    public function itShouldIntHex()
    {
        $str = 'lucid;g=0xff';
        $expr = new FilterExpression($str);
        $this->assertEquals(['lucid' => ['g' => 255]], $expr->all());
    }

    /** @test */
    public function itShoulPassStringOptVals()
    {
        $str = 'lucid;g=string';
        $expr = new FilterExpression($str);
        $this->assertEquals(['lucid' => ['g' => 'string']], $expr->all());
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowOnInvalidExpressionArg()
    {
        $expr = new FilterExpression(100);
    }

    /**
     * @test
     * @dataProvider paramProvider
     */
    public function itShouldTransfromToArray($params, $expexted)
    {
        $expr = new FilterExpression($params);

        $this->assertEquals($expexted, $expr->toArray());
        $this->assertEquals($expexted, $expr->all());
    }

    /** @test */
    public function itShouldRecognizeHexValues()
    {
        $params = 'a;b=#fff;c=000000:b;d=0ef';
        $expr = new FilterExpression($params);

        $parsed = $expr->toArray();

        $this->assertEquals(hexdec('ffffff'), $parsed['a']['b']);
        $this->assertEquals(hexdec('000000'), $parsed['a']['c']);
        $this->assertEquals(hexdec('00eeff'), $parsed['b']['d']);
    }

    public function paramProvider()
    {
        return [[
            'circle;o=0.4;b=false:gscale;b=1:foo',
            [
                'circle' => [
                    'o' => 0.4,
                    'b' => false
                ],
                'gscale' => [
                    'b' => 1
                ],
                'foo' => [
                ]
            ]],[

            'c:b:a',
            [
                'c' => [],
                'b' => [],
                'a' => [],
            ]],[
            'c;o',
            [
                'c' => ['o' => null]
                ]
            ]
        ];
    }
}
