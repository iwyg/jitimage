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
use org\bovigo\vfs\vfsStream;
use Thapp\JitImage\Driver\Scaling;
use Thapp\JitImage\Driver\DriverInterface;
use Thapp\Test\JitImage\Providers\DriverTestProvider;

/**
 * Class: JitImageDriverTest
 *
 * @uses TestCase
 * @abstract
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
abstract class JitImageDriverTest extends TestCase
{
    use DriverTestProvider;
    /**
     * driver
     *
     * @var \Thapp\ImDriver\Driver\DriverInterface
     */
    protected $driver;

    /**
     * fileUrl
     *
     * @var string
     */
    protected $testFile;

    /**
     * loaderMock
     *
     * @var mixed
     */
    protected $loaderMock;

    /**
     * sourceFile
     *
     * @var mixed
     */
    protected $sourceFile;

    /**
     * driverClean
     *
     * @var bool
     */
    protected $driverClean;

    /**
     * @test
     */
    abstract public function testLoad();

    /**
     * setUp
     *
     * @access protected
     * @return mixed
     */
    protected function setUp()
    {
        $this->fileRoot = vfsStream::setup('images');
        $this->fileUrl  = vfsStream::url('images');

        $this->loaderMock = m::mock('Thapp\JitImage\Driver\SourceLoaderInterface');
        $this->loaderMock->shouldReceive('load')->andReturnUsing(function ($url) {
            $this->driverClean = false;
            return $url;
        });
        $this->loaderMock->shouldReceive('clean')->andReturnUsing(function () {
            $this->driverClean = true;
        });
    }
    /**
     * tearDown
     *
     * @access protected
     * @return mixed
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (isset($this->driver)) {
            $this->driver->clean();
        }
        if (file_exists($this->sourceFile)) {
            @unlink($this->sourceFile);
        }
        if (file_exists($this->testFile)) {
            @unlink($this->testFile);
        }
    }

    /**
     * @test
     */
    public function testDriverClean()
    {
        $this->driver->load($this->createTestImage(400, 400));
        $this->driver->setTargetSize(200, 200);
        $this->driver->process();

        $this->assertFalse($this->driverClean);

        $this->driver->clean();

        $this->assertTrue($this->driverClean);

        $this->assertNull($this->getPropertyValue('source', $this->driver));
        $this->assertNull($this->getPropertyValue('resource', $this->driver));
        $this->assertNull($this->getPropertyValue('targetSize', $this->driver));
        $this->assertNull($this->getPropertyValue('outputType', $this->driver));
        $this->assertNull($this->getPropertyValue('sourceAttributes', $this->driver));
        $this->assertFalse($this->getPropertyValue('processed', $this->driver));

    }

    /**
     * @test
     */
    public function testGetImageData()
    {
        $this->driver->load($file = $this->getTestPattern());
        $this->assertEquals(file_get_contents($file), $this->driver->getImageBlob());
    }

    /**
     * @test
     */
    public function testImageHasBeenProcesses()
    {
        $this->driver->load($file = $this->getTestPattern());
        $this->assertFalse($this->driver->isProcessed());
        $this->runImageFilter('resize', 200, 200);
        $this->assertTrue($this->driver->isProcessed());
    }

    /**
     * @test
     */
    public function testParalellProcess()
    {
        $imageA = $this->createTestImage(400, 400);
        $imageB = $this->createTestImage(400, 600);

        $this->driver->load($imageA);
        $this->runImageFilter('resize', 200, 0);

        list($tw, $th) = getimagesize($this->writeTestImage($this->driver));
        $this->assertSame([$tw, $th], [200, 200]);

        $this->driver->clean();

        $this->driver->load($imageB);

        $this->runImageFilter('resize', 200, 0);

        list($tw, $th) = getimagesize($this->writeTestImage($this->driver));
        $this->assertSame([$tw, $th], [200, 300]);

    }

    /**
     * @dataProvider resizeFilterParameterProvider
     */
    public function testFilterResize($w, $h,  $nw, $nh, array $expected)
    {
        $image = $this->createTestImage($w, $h);
        $this->driver->load($image);
        $this->runImageFilter('resize', $nw, $nh);

        list($tw, $th) = getimagesize($this->writeTestImage($this->driver));
        $this->assertSame([$tw, $th], $expected);
    }

    /**
     * @dataProvider resizeToFitFilterParameterProvider
     */
    public function testFilterResizeToFit($w, $h,  $nw, $nh, array $expected)
    {
        $image = $this->createTestImage($w, $h);
        $this->driver->load($image);
        $this->runImageFilter('resizeToFit', $nw, $nh);


        list($tw, $th) = getimagesize($this->writeTestImage($this->driver));
        $this->assertSame([$tw, $th], $expected);
    }

    /**
     * @test
     * @dataProvider percentualResizeProvider
     */
    public function testFilterPercentualResize($w, $h,  $percent, $expected)
    {
        $image = $this->createTestImage($w, $h);
        $this->driver->load($image);
        $this->runImageFilter('percentualScale', $percent);

        list($tw, $th) = getimagesize($this->writeTestImage($this->driver));

        $this->assertSame($expected, [$tw, $th]);

    }

    /**
     * @test
     * @dataProvider pixelLimitProvider
     */
    public function testFilterPixelLimit($w, $h,  $limit)
    {
        $image = $this->createTestImage($w, $h);
        $this->driver->load($image);
        $this->runImageFilter('resizePixelCount', $limit);

        list($tw, $th) = getimagesize($this->writeTestImage($this->driver));

        $this->assertTrue(($tw * $th) <= $limit);

    }

    /**
     * @dataProvider imageTypeProvider
     */
    public function testGetOutputFormat($setType, $type, $mime)
    {
        $image = $this->createTestImage();
        $this->driver->load($image);

        if (!is_null($setType)) {
            $this->driver->setOutputType($setType);
        }

        $this->assertSame($type, $this->driver->getOutputType());
    }

    /**
     * @dataProvider imageTypeProvider
     */
    public function testGetOutputMimeType($setType, $type, $mime)
    {
        $image = $this->createTestImage();
        $this->driver->load($image);

        if (!is_null($setType)) {
            $this->driver->setOutputType($setType);
        }

        $this->assertSame($mime, $this->driver->getOutputMimeType());
    }

    /**
     * @dataProvider targetColors
     */
    public function testFilterCrop($gravity, $r, $g, $b)
    {
        $image = $this->getTestPattern();

        $this->driver->load($image);
        extract($this->driver->getInfo());

        $this->runImageFilter('crop', $width / 3, $height / 3, [$gravity]);

        $asset = $this->loadPatternAsset($this->driver);

        $this->assertSame($this->colorAt($asset, 0, 0), [$r, $g, $b]);
        $this->assertSame($this->colorAt($asset, 0, 199), [$r, $g, $b]);
        $this->assertSame($this->colorAt($asset, 199, 0), [$r, $g, $b]);
        $this->assertSame($this->colorAt($asset, 199, 199), [$r, $g, $b]);

        imagedestroy($asset);
    }


    /**
     * @test
     * @dataProvider cropScaleProvider
     */
    public function testFilterCropScale($gravity, $rgb)
    {
        $image = $this->getTestPattern();

        $this->driver->load($image);

        $this->runImageFilter('cropScale', 1200, 400, [$gravity]);
        $asset = $this->loadPatternAsset($this->driver);

        // compensate coordinates for color seaming
        $this->assertSame($this->colorAt($asset, 1, 1),     $rgb[0]);
        $this->assertSame($this->colorAt($asset, 1, 398),   $rgb[0]);
        $this->assertSame($this->colorAt($asset, 401, 1),   $rgb[1]);
        $this->assertSame($this->colorAt($asset, 401, 398), $rgb[1]);
        $this->assertSame($this->colorAt($asset, 804, 4),   $rgb[2]);
        $this->assertSame($this->colorAt($asset, 804, 398), $rgb[2]);

        $this->driver->clean();
        imagedestroy($asset);
    }

    /**
     * @dataProvider imageTypeProvider
     */
    public function testGetInfoType($what, $imageType, $mime)
    {
        $image = $this->createTestImage(200, 200, $imageType);
        $this->driver->load($image);

        extract($this->driver->getInfo());

        $this->assertSame($mime, $type);
    }

    /**
     * testGetInfoDimenstios
     *
     * @param mixed $what
     * @param mixed $imageType
     * @param mixed $mime
     * @access public
     * @return mixed
     * @dataProvider sizeRatioProvider
     */
    public function testGetInfoDimenstios($w, $h, $imageRatio)
    {
        $image = $this->createTestImage($w, $h);
        $this->driver->load($image);

        extract($this->driver->getInfo());

        $this->assertSame($imageRatio, $ratio);
    }

    /**
     * @dataProvider imageTypeProvider
     */
    public function testGetInfoModifiedOutPutType($what, $imageType, $mime)
    {
        $image = $this->createTestImage();
        $this->driver->load($image);

        $this->driver->setOutputType($imageType);

        extract($this->driver->getInfo());

        $this->assertSame('image/jpeg', $type);
    }

    /**
     * runImageFilter
     *
     * @param mixed $name
     * @param mixed $w
     * @param mixed $h
     * @param array $arguments
     * @access protected
     * @return mixed
     */
    protected function runImageFilter($name, $w = null, $h = null, array $arguments = [])
    {
        $this->driver->setTargetSize($w, $h);
        $this->driver->filter($name, $arguments);
        $this->driver->process();
    }

    /**
     * createTestImage
     *
     * @access protected
     * @return mixed
     */
    protected function createTestImage($width = 200, $height = 200, $type = 'jpg')
    {
        $image = imagecreatetruecolor($width, $height);

        ob_start();

        $fn = $this->getCreateFileFunction($type);
        $fn($image);
        $contents = ob_get_contents();

        ob_end_clean();

        return $this->createFile($contents);

    }

    /**
     * createFile
     *
     * @param mixed $file
     * @param mixed $contents
     * @access protected
     * @return mixed
     */
    protected function createFile($contents = null)
    {
        $f = tempnam(sys_get_temp_dir(), 'tests_jit_');
        file_put_contents($f, $contents);
        $this->sourceFile = $f;
        return $f;
    }

    /**
     * writeTestImage
     *
     * @param ImageDriverInterface $driver
     * @access protected
     * @return string
     */
    protected function writeTestImage(DriverInterface $driver)
    {
        $f = tempnam(sys_get_temp_dir(), 'target_jit_');
        file_put_contents($f, $driver->getImageBlob());
        $this->testFile = $f;
        return $f;
    }

    private function getCreateFileFunction($type)
    {
        switch(true) {
        case preg_match('#jpe?g#', $type):
            return 'imagejpeg';
        case preg_match('#png#', $type):
            return 'imagepng';
        case preg_match('#gif#', $type):
            return 'imagegif';
        }
    }


    protected function loadPatternAsset(\Thapp\JitImage\Driver\DriverInterface $driver)
    {
        return imagecreatefromstring($driver->getImageBlob());

    }

    protected function colorAt($resource, $x, $y)
    {
        $rgb = imagecolorat($resource, $x, $y);
        $col = imagecolorsforindex($resource, $rgb);
        $colors = array_values($col);
        array_pop($colors);
        return $colors;
    }

    /**
     * getTestPattern
     *
     * @param mixed $image
     * @access protected
     * @return resource
     */
    protected function getTestPattern()
    {
        return __DIR__ . '/assets/pattern.png';
    }
}
