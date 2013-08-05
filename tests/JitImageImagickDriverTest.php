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
use Thapp\JitImage\Driver\ImagickDriver;

/**
 * Class: JitImageImDriverTest
 *
 * @uses JitImageDriverTest
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageImagickDriverTest extends JitImageDriverTest
{

    /**
     * setUp
     *
     * @access protected
     * @return mixed
     */
    protected function setUp()
    {

        if (!class_exists('\Imagick')) {
            $this->markTestSkipped();
        }

        parent::setUp();
        $this->driver = new ImagickDriver($this->loaderMock);
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
        $this->assertInstanceOf('\Imagick', $resource);
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
