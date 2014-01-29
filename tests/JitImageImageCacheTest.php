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
use Thapp\JitImage\ImageInterface;
use Thapp\JitImage\Cache\ImageCache;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @class JitImageSegmentCacheTest
 */
class JitImageCacheTest extends TestCase
{
    /**
     * path
     *
     * @var string
     */
    protected $path;
    /**
     * files
     *
     * @var \Illuminate\Filesystem\Filesystem;
     */
    protected $files;

    /**
     * image
     *
     * @var \Thapp\JitImage\Cache\ImageCache
     */
    protected $image;

    /**
     * cache
     *
     * @var \Thapp\JitImage\ImageInterface;
     */
    protected $cache;

    /**
     * @test
     */
    public function testCachePut()
    {
        $key = $this->cache->createKey('path/image.jpg', 'somevals');
        $this->cache->put($key, 'foo');
        $this->assertFileExists($this->path . '/' . substr($key, 0, 8). '/' . substr($key, 9));
    }

    /**
     * @test
     */
    public function testCacheDirExists()
    {
        $this->assertFileExists($this->path);
    }

    /**
     * @test
     */
    public function testCachePurge()
    {
        $key = $this->cache->createKey('path/image.jpg', 'somevals');
        $this->cache->put($key, 'foo');
        list($path, $file) = $this->getPathAndFile($key);

        $key = $this->cache->createKey('path/image2.jpg', 'somevals');
        $this->cache->put($key, 'foo');
        list($path2, $file2) = $this->getPathAndFile($key);


        $this->assertTrue(file_exists($path));
        $this->assertTrue(file_exists($path2));

        $this->cache->purge();

        $this->assertFalse(file_exists($path));
        $this->assertFalse(file_exists($path2));

    }

    /**
     * @test
     */
    public function testCacheDelete()
    {
        $key = $this->cache->createKey('path/image.jpg', 'somevals');
        $this->cache->put($key, 'foo');
        list($path, $file) = $this->getPathAndFile($key);

        $key = $this->cache->createKey('path/image2.jpg', 'somevals');
        $this->cache->put($key, 'foo');
        list($path2, $file2) = $this->getPathAndFile($key);

        $this->cache->delete('path/image.jpg');

        $this->assertFalse(file_exists($path));
        $this->assertTrue(file_exists($path2));
    }

    protected function getPathAndFile($key)
    {
        return [$this->path . '/' . substr($key, 0, 8), substr($key, 9)];
    }

    /**
     * @test
     */
    public function testCacheGetRaw()
    {
        $key = $this->cache->createKey('path/image.jpg', 'somevals');
        $this->cache->put($key, 'foo');

        $this->image->shouldReceive('getImageBlob')->andReturn('foo');

        $this->assertEquals('foo', $this->cache->get($key, true));
    }

    /**
     * @test
     * @dataProvider keyDataProvider
     */
    public function testCreateKey($src, $vars, $prefix, $suffix)
    {
        $key = $this->cache->createKey($src, $vars, $prefix, $suffix);

        $this->assertEquals(8, strpos($key, '.'));
        $this->assertEquals(8 + strlen($prefix) + 20, strlen($key));
    }

    public function keyDataProvider()
    {
        return [
            ['path/image.jpg', 'somevals', 'prefix', 'jpg'],
            ['path/image.jpg', 'somevals', 'jit', 'png'],
            ['path/image.jpg', 'somevals', 'foo', 'gif'],
            ['path/image.jpg', 'somevals', 'bar', 'somesonnnn']
        ];
    }


    protected function setUp()
    {
        // cannot use streamwrapper filesystem since it doesn't support glob
        $this->files = new Filesystem;
        $this->path  = __DIR__ . '/testCache';


        //if (!$this->files->exists($this->path)) {
            //$this->files->makeDirectory($this->path);
        //}

        $image = m::mock('Thapp\JitImage\ImageInterface');
        $image->shouldReceive('close');
        $image->shouldReceive('load');

        $this->image = $image;

        $this->cache = new ImageCache($this->image, $this->files, $this->path);
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->files->exists($this->path)) {
            $this->files->remove($this->path);
        }
    }
}
