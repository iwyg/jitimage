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
use Thapp\Image\Geometry\Size;
use Thapp\Image\Geometry\Point;
use Thapp\Image\Geometry\Gravity;
use Thapp\Image\Geometry\SizeInterface;
use Thapp\Image\Geometry\PointInterface;
use Thapp\Image\Color\ColorInterface;
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
     */
    public function __construct(SourceInterface $source, FilterResolverInterface $filters = null, array $options = [])
    {
        $this->source = $source;
        $this->filters = $filters;
        $this->options = $options;
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        $this->unload();
    }

    /**
     * {@inheritdoc}
     */
    public function load(FileResourceInterface $resource)
    {
        $this->unload();
        $this->image = $this->source->read($resource->getHandle());
        $this->resource = $resource;
    }

    /**
     * Get output dimensions in width and height
     *
     * @return array
     */
    public function getTargetSize()
    {
        $this->loaded();

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
        return $this->image->getBlob($this->getFileFormat(), $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileFormat()
    {
        if (null === $this->targetFormat) {
            $this->targetFormat = $this->image->getFormat();
        }

        return static::formatToExtension($this->targetFormat);
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

        if (0 !== min($h, $w)) {
            $size = new Size($w, $h);
        } else {
            $size = $this->image->getSize()->getSizeFromRatio($w, $h);
        }

        $this->doResize($size);
    }

    /**
     * crop
     *
     * @param mixed $gravity
     * @param mixed $background
     *
     * @return void
     */
    protected function crop($gravity, $background = null)
    {
        $this->processed = true;

        list ($w, $h) = $this->targetSize;
        $size = new Size($w, $h);

        $this->image->setGravity($gravity = new Gravity($gravity));

        $color = $background ? $this->image->getPalette()->getColor($background) : null;

        if ($this->image->hasFrames()) {
            foreach ($this->image->frames()->coalesce() as $frame) {
                $frame->edit()->crop($size, null, $color);
            }
        } else {
            $this->image->edit()->crop($size, null, $color);
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

        $target = new Size($w, $h);
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
        $this->doResize($size->fit(new Size($w, $h)));
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
     * @param Size $size
     *
     * @return void
     */
    protected function doResize(SizeInterface $size)
    {
        if ($this->image->hasFrames()) {
            foreach ($this->image->frames()->coalesce() as $frame) {
                $frame->edit()->resize($size);
            }
        } else {
            $this->image->edit()->resize($size);
        }
    }

    protected function unload()
    {
        if (null !== $this->image) {
            $this->image->destroy();
            $this->image  = null;
        }

        parent::unload();
    }

}
