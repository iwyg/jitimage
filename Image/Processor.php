<?php

/*
 * This File is part of the Thapp\JitImage\Image package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image;

use Thapp\JitImage\AbstractProcessor;
use Thapp\Image\Metrics\Box;
use Thapp\Image\Metrics\Point;
use Thapp\Image\Metrics\Gravity;
use Thapp\Image\Metrics\BoxInterface;
use Thapp\Image\Metrics\PointInterface;
use Thapp\Image\Color\ColorInterface;
use Thapp\Image\Color\Hex;
use Thapp\Image\Driver\ImageInterface;
use Thapp\Image\Driver\SourceInterface;
use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Resolver\FilterResolverInterface;
use Thapp\JitImage\Resource\FileResourceInterface;

/**
 * @class Processor
 *
 * @package Thapp\JitImage\Image
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Processor extends AbstractProcessor
{
    private $image;
    private $source;

    /**
     * Constructor.
     *
     * @param SourceInterface $source
     * @param FilterResolverInterface $filters
     * @param array $options
     *
     * @return void
     */
    public function __construct(SourceInterface $source, FilterResolverInterface $filters = null, array $options = [])
    {
        $this->source = $source;
        $this->filters = $filters;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function load(FileResourceInterface $resource)
    {
        $this->processed = false;
        $this->image = $this->source->read($resource->getHandle());
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->targetFormat = null;
        $this->targetSize = null;
    }

    /**
     * Get output dimensions in width and height
     *
     * @return array
     */
    public function getTargetSize()
    {
        return [$this->image->getWidth(), $this->image->getHeight()];
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver()
    {
        return $this->image;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->image->get($this->getFileFormat(), $this->options);
    }

    public function getFileFormat()
    {
        if (null === $this->targetFormat) {
            $this->targetFormat = $this->image->getFormat();
        }

        return $this->targetFormat;
    }

    /**
     * {@inheritdoc}
     * @return ImageInterface
     */
    public function getCurrentImage()
    {
        return $this->image;
    }

    /**
     * getRatio
     *
     *
     * @return void
     */
    protected function getRatio()
    {
        return $this->image->getSize()->getRatio();
    }

    /**
     * resize
     *
     * @return void
     */
    protected function resize()
    {
        $this->processed = true;

        list ($w, $h) = $this->targetSize;

        $ratio = $this->getRatio();

        if (0 === max($h, $w)) {
            throw new \InvalidArgumentException();
        }

        if (0 === $w) {
            $size = $this->image->getSize()->increaseByHeight($h);
        } elseif (0 === $h) {
            $size = $this->image->getSize()->increaseByWidth($w);
        } else {
            $size = new Box($w, $h);
        }

        $this->doResize($size);
    }

    protected function crop($gravity, $background = null)
    {
        $this->processed = true;

        list ($w, $h) = $this->targetSize;
        $size = new Box($w, $h);

        $gravity = new Gravity($gravity);
        $this->image->gravity($gravity);

        $color = $background ? new Hex($background) : null;

        if ($this->image->hasFrames()) {
            foreach ($this->image->frames()->coalesce() as $frame) {
                $frame->gravity($gravity);
                $frame->crop($size, null, $color);
            }
        } else {
            $this->image->gravity($gravity);
            $this->image->crop($size, null, $color);
        }
    }

    /**
     * cropScale
     *
     * @param mixed $gravity
     *
     * @return void
     */
    protected function cropScale($gravity)
    {
        $this->processed = true;

        list ($w, $h) = $this->targetSize;

        $target = new Box($w, $h);
        $size = $this->image->getSize();

        $this->doResize($size->fill($target));

        $this->crop($gravity);
    }

    /**
     * resizeToFit
     *
     * @return void
     */
    protected function resizeToFit()
    {
        $this->processed = true;

        list ($w, $h) = $this->targetSize;

        $size = $this->image->getSize();
        $this->doResize($size->fit(new Box($w, $h)));
    }

    /**
     * resizePercentual
     *
     * @param mixed $percent
     *
     * @return void
     */
    protected function resizePercentual($percent)
    {
        $this->processed = true;

        $this->doResize($this->image->getSize()->scale($percent));
    }

    /**
     * resizePixelCount
     *
     * @param mixed $pixel
     *
     * @return void
     */
    protected function resizePixelCount($pixel)
    {
        $this->processed = true;

        $size = $this->image->getSize();
        $this->doResize($size->pixel($pixel));
    }

    /**
     * doResize
     *
     * @param BoxInterface $size
     *
     * @return void
     */
    protected function doResize(BoxInterface $size)
    {
        if ($this->image->hasFrames()) {
            foreach ($this->image->frames()->coalesce() as $frame) {
                $frame->resize($size);
            }
        } else {
            $this->image->resize($size);
        }
    }
}
