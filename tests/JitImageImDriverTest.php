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
use Thapp\JitImage\Driver\ImDriver;

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
class JitImageImDriverTest extends JitImageDriverTest
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

        $locator = m::mock('Thapp\JitImage\Driver\BinLocatorInterface');
        $locator->shouldReceive('getConverterPath')->andReturn($this->locateConvertBinary());
        $this->driver = new ImDriver($locator, $this->loaderMock);
    }

    /**
     * @test
     */
    public function testLoad()
    {
        $image = $this->createTestImage();

        $this->driver->load($image);

        $source = $this->getPropertyValue('source', $this->driver);
        $this->assertEquals($this->sourceFile, $source);
    }


    /**
     * @test
     * @dataProvider filterDataProvider
     */
    public function testOwnFilter($filter, $expectation = null)
    {
        return null;
    }

    /**
     * locateConvertBinary
     *
     * @access private
     * @return mixed
     */
    private function locateConvertBinary()
    {
        $paths = '/usr/local/bin,/usr/bin,/bin,./bin,../bin';
        $bin = null;

        foreach (explode(',', $paths) as $path) {

            if (file_exists($bin = $path . '/convert')) {
                break;
            }
        }

        return $bin;
    }

}
