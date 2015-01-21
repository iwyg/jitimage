<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Resource package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Resource;

use Thapp\JitImage\Resource\FileResource;

/**
 * @class FileResourceTest
 *
 * @package Thapp\JitImage\Tests\Resource
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FileResourceTest extends ResourceTest
{
    protected $handle;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);

        $this->assertInstanceof('Thapp\JitImage\Resource\ResourceInterface', $resource);
    }

    /** @test */
    public function itShouldGetHandle()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);

        $this->assertSame($handle, $resource->getHandle());
    }

    /** @test */
    public function itShouldGetContents()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);

        $this->assertSame('some text', $resource->getContents());
    }

    /** @test */
    public function itShouldBeValid()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);

        $this->assertTrue($resource->isValid());

        fclose($resource->getHandle());

        $this->assertFalse($resource->isValid());
    }

    /** @test */
    public function itShouldGetModTime()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);

        $this->assertSame(time(), $resource->getLastModified());
    }

    /** @test */
    public function itShouldBeFresh()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);
        $time = time();

        touch($resource->getPath(), $time - 100);

        $this->assertSame($time - 100, $resource->getLastModified($time));
        $this->assertTrue($resource->isFresh($time));
        $this->assertFalse($resource->isFresh($time - 100));
    }

    /** @test */
    public function itShouldGetFileName()
    {
        $this->handle = $handle = fopen(__FILE__, 'r');
        $resource = new FileResource($handle);

        $this->assertSame(basename(__FILE__), $resource->getFileName());
    }
    /** @test */
    public function itShouldReturnEmptyStringAfterBeeingClosed()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);

        fclose($resource->getHandle());
        $this->assertSame('', $resource->getContents());
    }

    /** @test */
    public function itShouldBeReadOnly()
    {
        $handle = $this->newHandle('some text');
        $resource = new FileResource($handle);
        $time = time();

        $resource->setContents('new text');
        $resource->setLastModified($time + 10);
        $resource->setFresh(true);

        $this->assertSame('some text', $resource->getContents());
        $this->assertSame($time, $resource->getLastModified());
        $this->assertFalse($resource->isFresh($time));
    }

    /**
     * newHandle
     *
     * @param mixed $contents
     *
     * @return void
     */
    protected function newHandle($contents = null)
    {
        $handle = tmpfile();

        if (null !== $contents) {
            fwrite($handle, $contents);
        }

        rewind($handle);

        $this->handle = $handle;

        return $handle;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
