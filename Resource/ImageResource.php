<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage\Resource package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

/**
 * @class ImageResource extends AbstractResource
 * @see AbstractResource
 *
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Resource
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImageResource extends AbstractResource implements ImageResourceInterface
{
    protected $width;
    protected $height;

    /**
     * Constructor.
     *
     * @param string $path
     * @param int $width
     * @param int $height
     */
    public function __construct($path = null, $width = null, $height = null)
    {
        $this->path = $path;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        if (null !== $this->width) {
            return $this->width;
        }

        return $this->widthFromPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        if (null !== $this->height) {
            return $this->height;
        }

        return $this->heightFromPath();
    }

    /**
     * widthFromPath
     *
     * @return int
     */
    protected function widthFromPath()
    {
        $this->detectSize();

        return $this->width;
    }

    /**
     * heightFromPath
     *
     * @return int
     */
    protected function heightFromPath()
    {
        $this->detectSize();

        return $this->height;
    }

    /**
     * detectSize
     *
     * @return void
     */
    protected function detectSize()
    {
        if (null !== $this->getPath() && $this->isLocal()) {
            $size = getimagesize($this->getPath());
        } else {
            $size = getimagesizefromstring($this->getContents());
        }

        $this->width = $size[0];
        $this->height = $size[1];
    }

    protected function setDimenstion($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }
}
