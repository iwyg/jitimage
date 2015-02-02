<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Framework\Laravel\Resolver package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Framework\Laravel\Resolver;

use Thapp\JitImage\Tests\Resolver\CacheResolverTest;
use Thapp\JitImage\Framework\Laravel\Resolver\LazyCacheResolver;

/**
 * @class LazyCacheResolverTest
 *
 * @package Thapp\JitImage\Tests\Framework\Laravel\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class LazyCacheResolverTest extends CacheResolverTest
{
    protected $app;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Resolver\CacheResolverInterface', $this->newResolver());
    }

    /** @test */
    public function itShouldResolverACacheByAlias()
    {
        $this->getApp()->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(function ($key) {
                if ('config' === $key) {
                    return $this->mockConfig();
                }
            }));

        //parent::itShouldResolverACacheByAlias();
    }

    /**
     * newResolver
     *
     * @return CacheResolver
     */
    protected function newResolver()
    {
        return new LazyCacheResolver($this->getApp(), $this->mockPathResolver());
    }

    protected function mockPathResolver()
    {
        $res = $this->getMock('Thapp\JitImage\Resolver\PathResolverInterface');
        $res->method('has')->willReturn(true);

        return $res;
    }

    protected function mockConfig()
    {
        $config = $this->getMock('Config', ['get', 'offsetGet']);

        return $config;
    }

    /**
     * getApp
     *
     * @return Illuminate\Contracts\Foundation\Application
     */
    protected function getApp()
    {
        if (null === $this->app) {
            $app = $this->getMockBuilder('Thapp\JitImage\Tests\Stubs\LVA')
                ->disableOriginalConstructor()
                ->setMethods(['offsetGet'])
                ->getMockForAbstractClass();

            $this->app = $app;
        }

        return $this->app;
    }

    protected function setUp()
    {
        $this->markTestSkipped();
    }
    protected function tearDown()
    {
        $this->app = null;
    }
}
