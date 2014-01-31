<?php

/**
 * This File is part of the tests package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Tests\JitImage;

use Mockery as m;
use \Thapp\JitImage\ImageInterface;
use \Thapp\JitImage\ResolverInterface;
use \Thapp\JitImage\Proxy\ProxyImage;

class ProxyImageTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @dataProvider methodProvider
     */
    public function testInvokeMethod($method, array $arguments = null, $shouldFail = false)
    {
        $mock = m::mock('\Thapp\JitImage\ImageInterface');

        if ($method !== 'close') {
            $mock->shouldReceive('close');
        }

        $mock->shouldReceive($method)->andReturnUsing(function () use ($shouldFail, $method) {
            if ($shouldFail) {
                $this->fail('should not be called directly on this mock object');
                return true;
            }
        });

        try {
            $proxy = new ProxyImage(function () use ($mock) {
                return $mock;
            });
            if (!call_user_func_array([$proxy, $method], $arguments)) {
                $this->assertTrue(true);
            }
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function methodProvider()
    {
        $mock = m::mock('Thapp\JitImage\ResolverInterface');
        $mock->shouldReceive('__toString')->andReturn('');

        return [
            ['close', [null], true],
            ['process', [$mock], false],
            ['load', ['some file'], false]
        ];
    }
}
