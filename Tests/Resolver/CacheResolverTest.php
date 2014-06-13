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
use \Thapp\JitImage\Resolver\CacheResolver;

/**
 * @class ImageResolverTest
 * @package Thapp\JitImage
 * @version $Id$
 */
class CacheResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $resolver = new CacheResolver([]);

        $this->assertInstanceof('\Thapp\JitImage\Resolver\ResolverInterface', $resolver);
        $this->assertInstanceof('\IteratorAggregate', $resolver);
    }

    /** @test */
    public function cachesShouldBeAbbale()
    {
        $resolver = new CacheResolver([]);
        $resolver->add('path', $c = m::mock('\Thapp\Image\Cache\CacheInterface'));

        $this->assertSame($c, $resolver->resolve('path'));

        $resolver = new CacheResolver([]);
        $resolver->set(['path' => $c = m::mock('\Thapp\Image\Cache\CacheInterface')]);

        $this->assertSame($c, $resolver->resolve('path'));
    }

    /** @test */
    public function cachesShouldResolveCaches()
    {
        $c = m::mock('\Thapp\Image\Cache\CacheInterface');

        $resolver = new CacheResolver([
            'path' => $c
        ]);

        $this->assertSame($c, $resolver->resolve('path'));
        $this->assertNull($resolver->resolve('pat'));
    }

    /** @test */
    public function itShouldBeIteratable()
    {

        $resolver = new CacheResolver($p = [
            'path' => $c = m::mock('\Thapp\Image\Cache\CacheInterface')
        ]);

        $res = [];

        foreach ($resolver as $path => $cache) {
            $res[$path] = $cache;
        }

        $this->assertSame($res, $p);
    }
}
