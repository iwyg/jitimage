<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Resolver;

use \Mockery as m;
use \Thapp\JitImage\Resolver\ImageResolver;

/**
 * @class ImageResolverTest
 * @package Thapp\JitImage
 * @version $Id$
 */
class ImageResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $resolver = new ImageResolver(
            m::mock('\Thapp\Image\ProcessorInterface')
        );

        $this->assertInstanceof('\Thapp\JitImage\Resolver\ParameterResolverInterface', $resolver);
    }

    /** @test */
    public function itShouldGetIstResolvers()
    {
        $resolver = new ImageResolver(
            $proc = m::mock('\Thapp\Image\ProcessorInterface'),
            $res  = m::mock('\Thapp\JitImage\Resolver\ResolverInterface')
        );

        $this->assertSame($proc, $resolver->getProcessor());
        $this->assertSame($res, $resolver->getCacheResolver());
    }

    /**
     * @test
     * @dataProvider paramsProvider
     */
    public function itIsExpectedThatTheProcessorIsCalledIfNoCache($params, $expected, $filter = null)
    {
        $failed = true;

        $resolver = new ImageResolver($proc = m::mock('\Thapp\Image\ProcessorInterface'));

        $proc->shouldReceive('load')->with('path/file.jpg');
        $proc->shouldReceive('getContents')->andReturn('');
        $proc->shouldReceive('isProcessed')->andReturn(true);
        $proc->shouldReceive('getLastModTime')->andReturn(time());
        $proc->shouldReceive('getMimeType')->andReturn('image/jpeg');
        $proc->shouldReceive('getSource')->andReturn('');

        $proc->shouldReceive('process')->andReturnUsing(function ($params) use (&$failed, $expected) {
            $this->assertSame($expected, $params);
            $failed = false;
        });

        $resolver->resolveParameters(['path', $params, 'file.jpg', $filter]);

        $this->assertFalse($failed);
    }

    /**
     * @test
     */
    public function itShouldValidateParams()
    {
        $resolver = new ImageResolver(
            $proc = m::mock('\Thapp\Image\ProcessorInterface'),
            $res  = m::mock('\Thapp\JitImage\Resolver\ResolverInterface'),
            $val  = m::mock('\Thapp\JitImage\Validator\ValidatorInterface')
        );

        $val->shouldReceive('validate')->with(2, [500, 500])->andReturn(false);

        $res->shouldreceive('resolve')->andReturn(null);

        try {
            $resolver->resolveParameters(['path', '2/500/500/5', 'file.jpg', null]);
        } catch (\OutOfBoundsException $e) {
            $this->assertSame('Parameters exceed limit', $e->getMessage());
            return;
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->fail('test slipped');
    }

    /** @test */
    public function itIsReturnCachedIfAppropriated()
    {
        $failed = true;

        $resolver = new ImageResolver(
            $proc = m::mock('\Thapp\Image\ProcessorInterface'),
            $res  = m::mock('\Thapp\JitImage\Resolver\ResolverInterface')
        );

        $cache = m::mock('\Thapp\Image\Cache\CacheInterface');

        $res->shouldreceive('resolve')->with('image')->andReturn($cache);

        $cache->shouldReceive('createKey')->with('path/file.jpg', '2/20/20/5/', 'jpg');
        $cache->shouldReceive('has')->andReturn(true);
        $cache->shouldReceive('get')->andReturnUsing(function () use (&$failed) {
            $failed = false;
            return m::mock('\Thapp\JitImage\Resource\ImageResource');
        });

        $this->assertInstanceof(
            '\Thapp\JitImage\Resource\ImageResource',
            $resolver->resolveParameters(['path', '2/20/20/5', 'file.jpg', null, 'image'])
        );

        $this->assertFalse($failed);
    }

    /** @test */
    public function itShouldResolveCached()
    {
        $failed = true;

        $resolver = new ImageResolver(
            $proc = m::mock('\Thapp\Image\ProcessorInterface'),
            $res  = m::mock('\Thapp\JitImage\Resolver\ResolverInterface')
        );

        $cache = m::mock('\Thapp\Image\Cache\CacheInterface');
        $cache->shouldReceive('has')->with('a.b')->andReturnUsing(function () use (&$failed) {
            $failed = false;
            return false;
        });

        $res->shouldreceive('resolve')->with('prefix')->andReturn($cache);

        $resolver->resolveCached(['prefix', 'a/b']);

        $this->assertFalse($failed);

        $failed = true;

        $resolver = new ImageResolver(
            $proc = m::mock('\Thapp\Image\ProcessorInterface'),
            $res  = m::mock('\Thapp\JitImage\Resolver\ResolverInterface')
        );

        $cache = m::mock('\Thapp\Image\Cache\CacheInterface');
        $cache->shouldReceive('get')->with('a.b')->andReturnUsing(function () use (&$failed) {
            $failed = false;
        });
        $cache->shouldReceive('has')->with('a.b')->andReturnUsing(function () {
            return true;
        });

        $res->shouldreceive('resolve')->with('prefix')->andReturn($cache);

        $resolver->resolveCached(['prefix', 'a/b']);

        $this->assertFalse($failed);
    }

    /** @test */
    public function itShouldReturnNullIfNocacheWasFound()
    {
        $resolver = new ImageResolver(
            $proc = m::mock('\Thapp\Image\ProcessorInterface'),
            $res  = m::mock('\Thapp\JitImage\Resolver\ResolverInterface')
        );

        $res->shouldreceive('resolve')->with('prefix')->andReturn(null);

        $this->assertNull($resolver->resolveCached(['prefix', 'a/b']));
    }

    public function paramsProvider()
    {
        return [
            [
                null,
                [
                'mode'       => '',
                'width'      => null,
                'height'     => null,
                'gravity'    => null,
                'background' => null,
                'filter'     => []
                ]
            ],
            [
                '0/100/100/fff',
                [
                'mode'       => 0,
                'width'      => null,
                'height'     => null,
                'gravity'    => null,
                'background' => null,
                'filter'     => []
                ]
            ],

            [
                '2/100/100/5',
                [
                'mode'       => 2,
                'width'      => 100,
                'height'     => 100,
                'gravity'    => 5,
                'background' => null,
                'filter'     => [
                    'circle' => [
                        'o' => 1.2
                        ]
                    ]
                ],
                'filter:circle;o=1.2'
            ],

            [
                '4/100/100/5/fff',
                [
                'mode'       => 4,
                'width'      => 100,
                'height'     => 100,
                'gravity'    => null,
                'background' => null,
                'filter'     => []
                ]
            ],

            [
                '3/100/100/5/fff',
                [
                'mode'       => 3,
                'width'      => 100,
                'height'     => 100,
                'gravity'    => 5,
                'background' => 'fff',
                'filter'     => []
                ]
            ],
        ];
    }

    protected function tearDown()
    {
        m::close();
    }
}
