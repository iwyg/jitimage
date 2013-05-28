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
use Thapp\JitImage\Driver\ImageSourceLoader;

/**
 * Class: JitImageImageSourceLoaderTest
 *
 * @uses TestCase
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageImageSourceLoaderTest extends TestCase
{
    /**
     * file
     *
     * @var string
     */
    protected $file;

    /**
     * loader
     *
     * @var \Thapp\JitImage\Driver\ImageSourceLoader
     */
    protected $loader;

    /**
     * create new Thapp\JitImage\Driver\ImageSourceLoader instance
     *
     * @access public
     * @return void
     */
    public function setUp()
    {
        $this->loader = new ImageSourceLoader;
    }

    /**
     * clean up temp files
     *
     * @access public
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        if (file_exists($this->file)) {
            @unlink($this->file);
        }
    }

    /**
     * testLoadRemoteSrouceShouldThrowException
     *
     * @test
     * @expectedException \Thapp\JitImage\Exception\ImageResourceLoaderException
     */
    public function testLoadRemoteSrouceShouldThrowException()
    {
        $url = 'http://domain.com/image.jpg';

        $this->loader->load($url);
    }

    /**
     * testLoadRemoteSrouceShouldThrowException
     *
     * @test
     * @expectedException \Thapp\JitImage\Exception\ImageResourceLoaderException
     */
    public function testLoadLoacalSrouceShouldThrowException()
    {
        $url = './file.jpg';

        $this->assertFalse($this->loader->load($url));
    }

    /**
     * testLoadRemoteSrouceShouldThrowException
     *
     * @test
     */
    public function testLoadRemoteSrouceShouldSucceed()
    {
        $url = 'https://www.google.de/images/srpr/logo4w.png';

        $this->assertFileExists($this->file = $this->loader->load($url));
    }

    /**
     * testLoadRemoteSrouceShouldThrowException
     *
     * @test
     */
    public function testLoadLoacalSourceShouldSucceeed()
    {
        $root = vfsStream::setup('images');
        $url  = vfsStream::url('images');


        $image = imagecreatetruecolor(200, 200);

        ob_start();
        imagejpeg($image);
        $contents = ob_get_contents();
        ob_end_clean();

        $file = $url . '/image.jpg';
        file_put_contents($file, $contents);

        $this->assertEquals($file, $this->loader->load($file));
    }

    /**
     * testLoadRemoteSrouceShouldThrowException
     *
     * @test
     */
    public function testCleanUp()
    {
        $url = 'https://www.google.de/images/srpr/logo4w.png';

        $this->file = $this->loader->load($url);

        if (file_exists($this->file)) {

            $this->loader->clean();
            $this->assertFalse(file_exists($this->file));

            return;
        }

        $this->markTestSkipped();
    }
}
