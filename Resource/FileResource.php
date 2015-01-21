<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

/**
 * @class FileResource
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FileResource extends AbstractResource implements FileResourceInterface
{
    /**
     * handle
     *
     * @var resource
     */
    protected $handle;

    /**
     * Constructor.
     *
     * @param resource $handle
     * @param string $mime
     * @param string $content
     */
    public function __construct($handle, $mime = null)
    {
        $this->handle = $handle;
        $meta = stream_get_meta_data($handle);

        $this->setPath(isset($meta['uri']) ? $meta['uri'] : null);
        $this->setMimeType($this->findMimeType($meta, $mime));
    }

    /**
     * Close open files handles
     *
     * @return void
     */
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isLocal()
    {
        if (null === ($path = $this->getPath())) {
            return false;
        }

        return stream_is_local($path);
    }

    /**
     * {@inheritdoc}
     */
    public function setFresh($fresh)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setContents($contents)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLastModified($time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified()
    {
        return $this->isLocal() ? filemtime($this->getPath()) : time();
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return is_resource($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!$this->isValid()) {
            return '';
        }

        $pos = ftell($this->handle);
        rewind($this->handle);
        $contents = stream_get_contents($this->handle);
        fseek($this->handle, $pos);

        return $contents;
    }

    /**
     * findMimeType
     *
     * @param array  $meta
     * @param string $mime
     *
     * @return string
     */
    protected function findMimeType(array $meta, $mime = null)
    {
        if (null === $mime && $this->isLocal()) {
            list($mime,) = explode(';', finfo_file($info = finfo_open(FILEINFO_MIME), $this->getPath()));

            finfo_close($info);
        }

        return $mime;
    }
}
