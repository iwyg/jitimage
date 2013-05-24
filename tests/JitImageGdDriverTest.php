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
use Thapp\JitImage\Driver\GdDriver;

/**
 * Class: JitImageGsDriverTest
 *
 * @uses JitImageDriverTest
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageGdDriverTest extends JitImageDriverTest
{

    /**
     * setUp
     *
     * @access protected
     * @return mixed
     */
    protected function setUp()
    {
        parent::setUp();

        $loader = m::mock('Thapp\JitImage\Driver\SourceLoaderInterface');
        $loader->shouldReceive('load')->andReturnUsing(function ($url) {
            return $url;
        });

        $this->driver = new GdDriver($loader);
    }

    /**
     * @test
     */
    public function testLoad()
    {
        $image = $this->createTestImage();

        $this->driver->load($image);

        $source = $this->getPropertyValue('source', $this->driver);
        $resource = $this->getPropertyValue('resource', $this->driver);

        $this->assertEquals($this->sourceFile, $source);
        $this->assertTrue(is_resource($resource));
    }

    /**
     * @test
     * @dataProvider filterDataProvider
     */
    public function testOwnFilter($filter, $expectation = null)
    {
        return null;
    }
}
