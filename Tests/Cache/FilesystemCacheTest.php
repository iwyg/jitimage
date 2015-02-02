<?php

/**
 * This File is part of the Thapp\Image\Tests\Cache package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Cache;


use org\bovigo\vfs\vfsStream;
use Thapp\JitImage\Cache\CacheInterface;
use Thapp\JitImage\Cache\FilesystemCache;

/**
 * @class FileystemCacheTest
 * @package Thapp\Image\Tests\Cache
 * @version $Id$
 */
class FileystemCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $fs;

    protected $cache;

    protected $rootPath;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\Cache\FilesystemCache', new FilesystemCache);
    }

    /** @test */
    public function itIsExpectedThat()
    {
        $key = 'foo.bar';

        $this->cache->set($key, $this->getProcMock());
        $this->assertTrue(file_exists($this->rootPath . '/foo/bar.jpg'));
    }

    /** @test */
    public function itIsAbleToRetreiveFromKey()
    {
        $key = 'foo.bar';

        $this->assertFalse($this->cache->has($key));
        $this->cache->set($key, $this->getProcMock());
        $this->assertTrue($this->cache->has($key));

        $this->assertInstanceof('\Thapp\JitImage\Resource\ResourceInterface', $this->cache->get($key));

        $cache = $this->newSystem();
        $this->assertTrue($cache->has($key));
    }

    /** @test */
    public function itShouldSetResourceAttributes()
    {
        $key = 'foo.bar';

        $this->cache->set($key, $c = $this->getProcMock());
        $res = $this->cache->get($key, CacheInterface::CONTENT_RESOURCE);

        $this->assertInstanceof('\Thapp\JitImage\Resource\CachedResource', $res);

        $this->cache->set($key, $c = $this->getProcMock());
        $res = $this->cache->get($key, CacheInterface::CONTENT_STRING);

        $this->assertSame('', $res);
    }

    /** @test */
    public function itShouldBeAbleToCreateKeys()
    {
        $key = $this->cache->createKey('image.jpg', 'prefix', 'string/image.jpg', 'jpg');

        $this->assertSame(31, strlen($key));
        $this->assertTrue(1 === substr_count($key, '.'));
    }

    /** @test */
    public function itShouldPurgeCached()
    {
        $keyA = $this->cache->createKey('image.jpg', 'prefix', 'string/image.jpg', 'jpg');
        $keyB = $this->cache->createKey('image.png', 'prefix', 'string/image.png', 'png');

        $this->cache->set($keyA, $this->getProcMock());
        $this->cache->set($keyB, $this->getProcMock());

        $ra = $this->cache->get($keyA);
        $rb = $this->cache->get($keyB);

        $this->cache->purge();

        $this->assertFalse(file_exists($ra->getPath()));
        $this->assertFalse(file_exists($rb->getPath()));
    }

    /** @test */
    public function itShouldDeleteSelectivly()
    {
        $keyA = $this->cache->createKey('image.jpg', 'prefix', 'string/image.jpg', 'jpg');
        $keyB = $this->cache->createKey('image.png', 'prefix', 'string/image.png', 'png');

        $this->cache->set($keyA, $this->getProcMock());
        $this->cache->set($keyB, $this->getProcMock());

        $ra = $this->cache->get($keyA);
        $rb = $this->cache->get($keyB);

        $this->cache->delete('image.jpg', 'prefix');

        $this->assertFalse(file_exists($ra->getPath()));
        $this->assertTrue(file_exists($rb->getPath()));
    }

    /** @test */
    public function deletingNonFilesShouldReturnFalse()
    {
        $key = $this->cache->createKey('image.jpg', 'prefix', 'string/image.jpg', 'jpg');

        $this->assertFalse($this->cache->delete('image.jpg', 'prefix'));

        $this->cache->set($key, $this->getProcMock());

        $this->assertTrue($this->cache->delete('image.jpg', 'prefix'));
    }

    /** @test */
    public function purgeShouldReturnFalseIfPathIsInvalid()
    {
        $cache = new FilesystemCache($this->rootPath . '/cache');

        $this->assertFalse($cache->purge());
    }

    protected function setUp()
    {
        $root = vfsStream::setup('root');
        $this->rootPath = vfsStream::url('root');

        $this->cache = $this->newSystem();
    }

    protected function newSystem()
    {
        return new FilesystemCache($this->rootPath);
    }

    protected function getProcMock()
    {
        $proc = $this->getMockBuilder('Thapp\JitImage\ProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $proc->method('getFileExtension')->willReturn('jpg');
        $proc->method('getTargetSize')->willReturn(['w' => 100, 'h' => 100]);
        $proc->method('getContents')->willReturn('');
        $proc->method('getMimeType')->willReturn('image/jpeg');
        $proc->method('getLastModTime')->willReturn(time());
        $proc->method('getFileFormat')->willReturn('jpeg');

        return $proc;
    }
}
