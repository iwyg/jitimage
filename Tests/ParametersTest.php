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

use Thapp\JitImage\Parameters;

/**
 * @class ParametersTest
 * @see \PHPUnit_Framework_TestCase
 *
 * @package Thapp\JitImage\Tests
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ParametersTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Parameters', new Parameters);
    }

    /**
     * @test
     * @dataProvider paramStringProvider
     */
    public function itShouldParseParamString($str, $expected)
    {
        $params = Parameters::fromString($str);

        $this->assertEquals($expected, $params->all());
    }

    public function paramStringProvider()
    {
        return [
            ['2/300/300/5', ['mode' => 2, 'width' => 300, 'height' => 300, 'gravity' => 5, 'background' => null]],
            ['0/400/499/4/19', ['mode' => 0, 'width' => null, 'height' => null, 'gravity' => null, 'background' => null]],
            ['3/300/300/5/fff', ['mode' => 3, 'width' => 300, 'height' => 300, 'gravity' => 5, 'background' => 'ffffff']],
            ['3/300/300/5/#fe0', ['mode' => 3, 'width' => 300, 'height' => 300, 'gravity' => 5, 'background' => 'ffee00']],
        ];
    }

    /** @test */
    public function itShouldParseParamsFromString()
    {
        $params = new Parameters;

        $params->setFromString('0/100');

        $this->assertSame([
            'mode'       => 0,
            'width'      => null,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params->setFromString('2/200/400/3');

        $this->assertSame([
            'mode'       => 2,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 3,
            'background' => null,
        ], $params->all());
    }

    /** @test */
    public function itShouldBeConvertibleToString()
    {
        $params = new Parameters;
        $params->setMode(2);
        $params->setTargetSize(200, 200);

        $this->assertSame('2/200/200/5', $params->asString());
        $this->assertSame('2/200/200/5', (string)$params);
    }

    /** @test */
    public function itShouldBeEmptyWhenCloned()
    {
        $params = new Parameters;
        $params->setMode(2);
        $params->setTargetSize(200, 200);

        $clone = clone($params);

        $this->assertSame([
            'mode'       => 0,
            'width'      => null,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $clone->all());
    }

    /** @test */
    public function itShouldSetAndSanitizeParams()
    {
        $params = new Parameters;

        $params->setMode(0);
        $params->setTargetSize(200, 400);

        $this->assertSame([
            'mode'       => 0,
            'width'      => null,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(1);
        $params->setTargetSize(200, 400);

        $this->assertSame([
            'mode'       => 1,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(2);
        $params->setTargetSize(200, 400);
        $params->setGravity(2);
        $params->setBackground('fff');

        $this->assertSame([
            'mode'       => 2,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 2,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(2);
        $params->setTargetSize(200, 400);

        $this->assertSame([
            'mode'       => 2,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 5,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(3);
        $params->setTargetSize(200, 400);
        $params->setBackground('abcdf');

        $this->assertSame([
            'mode'       => 3,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 5,
            'background' => null,
            ], $params->all());

        $params = new Parameters;

        $params->setMode(3);
        $params->setTargetSize(200, 400);
        $params->setBackground('fff');

        $this->assertSame([
            'mode'       => 3,
            'width'      => 200,
            'height'     => 400,
            'gravity'    => 5,
            'background' => 'fff',
        ], $params->all());

        $params = new Parameters;

        $params->setMode(4);
        $params->setTargetSize(400, 400);

        $this->assertSame([
            'mode'       => 4,
            'width'      => 400,
            'height'     => 400,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(5);
        $params->setTargetSize(400, 400);

        $this->assertSame([
            'mode'       => 5,
            'width'      => 400,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());

        $params = new Parameters;

        $params->setMode(6);
        $params->setTargetSize(400, 400);

        $this->assertSame([
            'mode'       => 6,
            'width'      => 400,
            'height'     => null,
            'gravity'    => null,
            'background' => null,
        ], $params->all());
    }
}
