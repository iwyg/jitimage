<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Cache package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Cache;

use Thapp\JitImage\Cache\CacheClearer;

/**
 * @class CacheClearerTest
 *
 * @package Thapp\JitImage\Tests\Cache
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class CacheClearerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof(
            'Thapp\JitImage\Cache\CacheClearer',
            new CacheClearer($this->mockResolver())
        );
    }

    /** @test */
    public function itSouldReturnFalseIfCacheIsNotFound()
    {
        $res = $this->mockResolver();
        $res->expects($this->once())->method('has')->with('my_name')->willReturn(true);
        $res->expects($this->once())->method('resolve')->with('my_name')->willReturn(false);

        $cleare = new CacheClearer($res);
        $this->assertFalse($cleare->clear('my_name'));
    }

    /** @test */
    public function itSouldReturnFalseIfCacheWontClearPath()
    {
        $cache = $this->mockCache();
        $cache->expects($this->once())->method('purge')->with()->willReturn(false);
        $res = $this->mockResolver();
        $res->expects($this->once())->method('has')->with('my_name')->willReturn(true);
        $res->expects($this->once())->method('resolve')->with('my_name')->willReturn($cache);

        $cleare = new CacheClearer($res);
        $this->assertFalse($cleare->clear('my_name'));
    }

    /** @test */
    public function itSouldReturnTrueIfCacheWillClearPath()
    {
        $cache = $this->mockCache();
        $cache->expects($this->once())->method('purge')->with()->willReturn(true);
        $res = $this->mockResolver();
        $res->expects($this->once())->method('has')->with('my_name')->willReturn(true);
        $res->expects($this->once())->method('resolve')->with('my_name')->willReturn($cache);

        $cleare = new CacheClearer($res);
        $this->assertTrue($cleare->clear('my_name'));
    }

    protected function mockResolver()
    {
        return $this->getMockBuilder('Thapp\JitImage\Resolver\CacheResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function mockCache()
    {
        return $this->getMockBuilder('Thapp\JitImage\Cache\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
