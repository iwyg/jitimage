<?php

/**
 * This File is part of the Thapp\JitImage\Tests\Resolver package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Resolver;

use Thapp\JitImage\Resolver\PathResolver;

/**
 * @class PathResolver
 * @package Thapp\JitImage\Tests\Resolver
 * @version $Id$
 */
class PathResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Resolver\ResolverInterface', new PathResolver);
    }

    /** @test */
    public function itShouldResolveARoutePathToABasePath()
    {
        $resolver = new PathResolver(
            [
                'images'  => 'path/to/images',
                'thumbs' => 'path/to/thumbs',
            ]
        );

        $this->assertSame('path/to/images', $resolver->resolve('images'));
        $this->assertSame('path/to/thumbs', $resolver->resolve('thumbs'));

        $this->assertSame('path/to/images', $resolver->resolve('/images'));
        $this->assertSame('path/to/thumbs', $resolver->resolve('/thumbs'));

        $this->assertNull($resolver->resolve('path'));
    }

    /** @test */
    public function itShouldReturnMappings()
    {
        $resolver = new PathResolver;
        $this->assertInternalType('array', $resolver->all());
    }
}
