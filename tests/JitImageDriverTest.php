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
use Thapp\JitImage\Driver\DriverInterface;

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
            return $url;
        });
        $this->loaderMock->shouldReceive('clean')->andReturn(null);
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

        //var_dump($this->driver->getOutputType());

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
     * @test
     * @dataProvider cropFilterParameterProvider
     */
    public function testFilterCrop($w, $h,  $nw, $nh, array $expected)
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @dataProvider cropScaleFilterParameterProvider
     */
    public function testFilterCropScale($w, $h,  $nw, $nh, array $expected)
    {
        $this->markTestIncomplete();
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
     * @test
     */
    public function testGetSourceSouldReturnOriginalSourceFile()
    {
        $image = $this->createTestImage();
        $this->loaderMock->shouldReceive('getSource')->andReturn($image);
        $this->driver->load($image);
        $this->assertSame($image, $this->driver->getSource());
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
     * filterDataProvider
     */
    public function filterDataProvider()
    {
        return [
            ['resize'],
            ['cropResize'],
            ['crop'],
            ['resizeToFit'],
        ];
    }

    /**
     * filterDataProvider
     */
    public function extFilterDataProvider()
    {
        return [
            ['gs'],
        ];
    }

    /**
     * fileProvider
     *
     * @access public
     * @return mixed
     */
    public function imageFileProvider()
    {
        return [
            ['image-1.jpg'],
            ['image-2.jpg']
        ];
    }

    /**
     * imageTypeProvider
     *
     * @access public
     * @return mixed
     */
    public function imageTypeProvider()
    {
        return [
         [null,  'jpeg', 'image/jpeg'],
         ['jpg', 'jpeg', 'image/jpeg'],
         ['png', 'png', 'image/png'],
         ['gif', 'gif', 'image/gif']
        ];
    }

    /**
     * percentualResizeProvider
     *
     * @access public
     * @return array
     */
    public function percentualResizeProvider()
    {
        return [
            [200, 200, 100, [200, 200]],
            [200, 200, 50, [100, 100]],
            [200, 200, 25, [50, 50]],
            [500, 325, 20, [100, 65]],
            [325, 500, 20, [65, 100]]
        ];
    }

    /**
     * sizeRatioProvider
     *
     * @access public
     * @return mixed
     */
    public function sizeRatioProvider()
    {
        return [
            [200, 200, 1.0],
            [200, 144, (float)(200 / 144)],
            [144, 220, (float)(144 / 220)],
            [530, 445, (float)(530 / 445)]
        ];
    }
    /**
     * resizeParameterProvider
     *
     * @access public
     * @return mixed
     */
    public function resizeFilterParameterProvider()
    {
        return [
            /*
             * width, height, scale w, scale h, expected outcome
             */
            [200, 200, 100, 0, [100, 100]],
            [200, 200, 100, 100, [100, 100]],
            [200, 200, 400, 400, [400, 400]],
            [200, 200, 400, 600, [400, 600]],
            [200, 200, 400, 0, [400, 400]],
            [400, 350, 600, 0, [600, 525]],
            [400, 350, 0, 600, [685, 600]],
            [350, 400, 600, 0, [600, 685]]
        ];
    }

    public function pixelLimitProvider()
    {
        return [
            /*
             * width, height, scale w, scale h, expected outcome
             */
            [200, 300, 100000],
            [300, 200, 100000],
            [200, 200, 100000]
        ];

    }

    /**
     * resizeToFitFilterParameterProvider
     *
     * @access public
     * @return mixed
     */
    public function resizeToFitFilterParameterProvider()
    {
        return [
            /*
             * width, height, scale w, scale h, expected outcome
             */
            [200, 200, 100, 40,  [40,  40]],
            [200, 100, 400, 400, [200, 100]],
            [200, 100, 400, 600, [200, 100]],
            [200, 100, 600, 400, [200, 100]],
            [200, 100, 100, 100, [100, 50]],
            [100, 200, 400, 600, [100, 200]],
            [100, 200, 100, 100, [50,  100]],
            [331, 500, 200, 200, [132, 200]],
            [500, 331, 200, 200, [200, 132]],
            [750, 500, 200, 200, [200, 133]],
            [500, 750, 200, 200, [133, 200]],
        ];
    }

    /**
     * cropScaleFilterParameterProvider
     *
     * @access public
     * @return mixed
     */
    public function cropScaleFilterParameterProvider()
    {
        return [
            [0, 0, 0, 0, []]
        ];
    }

    /**
     * cropFilterParameterProvider
     *
     * @access public
     * @return mixed
     */
    public function cropFilterParameterProvider()
    {
        return [
            [0, 0, 0, 0, []]
        ];
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
}
