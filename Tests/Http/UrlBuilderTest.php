<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Http;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\Http\UrlBuilder;

/**
 * @class UrlBuilderTest
 *
 * @package Thapp\JitImage\Tests\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Http\UrlBuilderInterface', new UrlBuilder);
    }

    /**
     * @test
     * @dataProvider getUriProvider
     */
    public function itShouldCreateUrl($expected, $src, $params, $filter = null, $prefix = '')
    {
        $builder = new UrlBuilder;
        $uri = $builder->getUri($src, Parameters::fromString($params), null, $prefix);

        $this->assertSame($expected, $uri);
    }

    /**
     * @test
     * @dataProvider getUriSignProvider
     */
    public function itShouldCreateUrlWithSignature($expected, $src, $params, $filter = null, $prefix = '', $sign = '')
    {
        $builder = new UrlBuilder($signer = $this->mockSigner());
        $signer->method('sign')->willReturn($expected = $expected.$sign);
        $uri = $builder->getUri($src, Parameters::fromString($params), null, $prefix);

        $this->assertSame($expected, $uri);
    }

    public function getUriProvider()
    {
        return [
            ['/images/0/image.jpg', 'image.jpg', '0', null, 'images'],
            ['/thumbs/2/400/400/5/image.jpg', 'image.jpg', '2/400/400/5', null, 'thumbs'],
        ];
    }

    public function getUriSignProvider()
    {
        return [
            ['/images/0/image.jpg', 'image.jpg', '0', null, 'images', '?token=my_token']
        ];
    }

    protected function mockSigner()
    {
        return $this->getMockBuilder('Thapp\JitImage\Http\HttpSignerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
