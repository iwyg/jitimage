<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

use \Thapp\JitImage\ImageInterface;
use \Thapp\JitImage\ResolverInterface;

/**
 * @class CachedImage
 * @package Thapp\JitImage
 * @version $Id$
 */
class CachedImage implements ImageInterface
{
    private $resource;
    private $source;
    private $finfo;
    private $closed;

    public function __construct($src = null)
    {
        $src && $this->load($src);
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@information}
     */
    public function load($source)
    {
        $this->close();
        $this->source = $source;
        $this->resource = finfo_open(FILEINFO_MIME_TYPE);

        $this->finfo = [
            'mime' => finfo_file($this->resource, $this->source),
            'lastmod' => filemtime($this->source)
        ];

        $this->closed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    protected function isClosed()
    {
        return $this->closed;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModTime()
    {
        if ($this->isClosed()) {
            return time();
        }
        return $this->finfo['lastmod'];
    }

    /**
     * {@information}
     */
    public function close()
    {
        if ($this->isClosed()) {
            return;
        }

        if (is_resource($this->resource)) {
            finfo_close($this->resource);
        };
        $this->source = null;
        $this->resource = null;
        $this->finfo = null;
        $this->closed = true;
    }

    /**
     * {@information}
     */
    public function getMimeType()
    {
        if (!$this->isClosed()) {
            return $this->finfo['mime'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isProcessed()
    {
        return false;
    }

    /**
     * {@information}
     */
    public function process(ResolverInterface $resolver)
    {
        throw new \LogicException(
            sprintf('calling process() on a cached image is not allowed, called with %s', get_class($resolver))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setQuality($quality)
    {
        throw new \LogicException(
            sprintf('calling setQuality() on a cached image is not allowed, called with %s', get_class($quality))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setFileFormat($format)
    {
        throw new \LogicException(
            sprintf('calling setFileFormat() on a cached image is not allowed, called with %s', get_class($format))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        return file_get_contents($this->source);
    }

    /**
     * {@inheritDoc}
     */
    public function getFileFormat()
    {
        if ($this->isClosed()) {
            return;
        }

        if (!isset($this->finfo['extension'])) {
            $this->finfo['extension'] = preg_replace(['~image/~', '~.*jpeg~i'], ['', 'jpg'], $this->getMimeType());
        }
        return $this->finfo['extension'];
    }

    /**
     * {@information}
     */
    public function getSourceFormat()
    {
        return $this->getFileFormat();
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceMimeTime()
    {
        return $this->getMimeType();
    }
}
