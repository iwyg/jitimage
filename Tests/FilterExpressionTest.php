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

    /**
     * @test
     * @dataProvider paramProvider
     */
    public function itShouldTransfromToArray($params, $expexted)
    {
        $expr = new FilterExpression($params);

        $this->assertEquals($expexted, $expr->toArray());
    }

    /** @test */
    public function itShouldRecognizeHexValues()
    {
        $params = 'a;b=#fff;c=000000:b;d=0ef';
        $expr = new FilterExpression($params);

        $parsed = $expr->toArray();

        $this->assertEquals('ffffff', $parsed['a']['b']);
        $this->assertEquals('000000', $parsed['a']['c']);
        $this->assertEquals('00eeff', $parsed['b']['d']);
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
