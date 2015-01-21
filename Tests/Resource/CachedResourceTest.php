<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Resource;

use Thapp\JitImage\Resource\CachedResource;

/**
 * @class CachedResourceTest
 * @package Thapp\Image
 * @version $Id$
 */
class CachedResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeImmutableAfterCreation()
    {
        $proc = $this->getProcMock('', 'application/octet-stream', $t = time());
        $res = new CachedResource($proc, null, '/path');

        $res->setLastModified(0);
        $res->setContents('bar');
        $res->setMimeType('image/jpeg');
        $res->setPath('/newpath');

        $this->assertSame('/path', $res->getPath());
        $this->assertSame('application/octet-stream', $res->getMimeType());
        $this->assertSame($t, $res->getLastModified());
        $this->assertSame('', $res->getContents());
    }

    /** @test */
    public function itShouldGetDimensions()
    {
        $proc = $this->getProcMock('', 'image/jpeg', $t = time());
        $res = new CachedResource($proc, null, '/path');

        $this->assertSame(100, $res->getWidth());
        $this->assertSame(100, $res->getHeight());
    }

    /** @test */
    public function itShouldGetKey()
    {
        $proc = $this->getProcMock('', 'image/jpeg', $t = time());
        $res = new CachedResource($proc, $key = ('key'.microtime(true)), '/path');

        $this->assertSame($key, $res->getKey());
    }

    /** @test */
    public function itShouldGetKeyAfterDeserialation()
    {
        $proc = $this->getProcMock('', 'image/jpeg', $t = time());
        $res = new CachedResource($proc, $key = ('key'.microtime(true)), '/path');

        $ser = serialize($res);
        $u_ser = unserialize($ser);

        $this->assertSame($key, $u_ser->getKey());
    }

    /** @test */
    public function itShouldGetFileName()
    {
        $proc = $this->getProcMock('', 'image/jpeg', $t = time());
        $res = new CachedResource($proc, $key = ('key'.microtime(true)), '/path/file');
        $this->assertSame('file', $res->getFileName());
    }

    protected function getProcMock($content = null, $mime = null, $time = null)
    {
        $proc = $this->getMockBuilder('Thapp\JitImage\ProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $proc->method('getContents')->willReturn($content);
        $proc->method('getMimeType')->willReturn($mime);
        $proc->method('getLastModTime')->willReturn($time);
        $proc->method('getTargetSize')->willReturn([100, 100]);

        return $proc;
    }
}
