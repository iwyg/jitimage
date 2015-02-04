<?php

/*
 * This File is part of the Thapp\JitImage\Imagine package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Imagine;

use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\LayersInterface;
use Imagine\Image\BoxInterface;
use Imagine\Image\PointInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Thapp\JitImage\AbstractProcessor;
use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Resolver\FilterResolverInterface;
use Thapp\JitImage\Resource\FileResourceInterface;

/**
 * @class Processor
 *
 * @package Thapp\JitImage\Imagine
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Processor extends AbstractProcessor
{
    private $image;
    private $imagine;

    /**
     * Constructor.
     *
     * @param ImagineInterface $imagine
     * @param FilterResolverInterface $filters
     * @param array $options
     */
    public function __construct(ImagineInterface $imagine, FilterResolverInterface $filters = null, array $options = [])
    {
        $this->imagine = $imagine;
        $this->filters = $filters;
        $this->options = $options;

    }

    /**
     * {@inheritdoc}
     */
    public function load(FileResourceInterface $resource)
    {
        $this->processed = false;
        $this->image = $this->imagine->read($resource->getHandle());
        $this->resource = $resource;
    }

    /**
     * Get the image driver
     *
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->image;
    }

    /**
     * get the filecontents of the image
     *
     * @return string
     */
    public function getContents()
    {
        return $this->image->get($this->getFileFormat(), $this->options);
    }

    /**
     * Get output dimensions in width and height
     *
     * @return array
     */
    public function getTargetSize()
    {
        $size = $this->image->getSize();

        return [$size->getWidth(), $size->getHeight()];
    }

    /**
     * getRatio
     *
     *
     * @return void
     */
    protected function getRatio()
    {
        $box = $this->image->getSize();

        return $this->ratio($box->getWidth(), $box->getHeight());
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

        if (0 === max($h, $w)) {
            throw new \InvalidArgumentException();
        }

        if (0 === $w) {
            $size = $this->image->getSize()->heighten($h);
        } elseif (0 === $h) {
            $size = $this->image->getSize()->widen($w);
        } else {
            $size = new Box($w, $h);
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
        // flatten image:
        $target = new Box($w, $h);
        $gravity = new Gravity($gravity);

        $size = $this->image->getSize();

        if (false === $size->contains($target)) {
            $color = $background ? $this->image->palette()->color($background) : null;

            if (null !== $color) {
                $alpha = ($background & 0xFF000000) >> 24;
                $color->dissolve(-(int)(100 - ($alpha / 127) * 100));
            }

            $size = $this->image->getSize();
            $this->extent($size, $target, $gravity->getPoint($size, $target), $color);

            return;
        }

        $point = $gravity->getPoint($size, $target);

        $this->doCrop($point, $target);
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

        $size = $this->image->getSize();

        $fill = (new Size($size->getWidth(), $size->getHeight()))->fill(new Box($w, $h));

        $this->doResize($fill);

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
        $fit = (new Size($size->getWidth(), $size->getHeight()))->fit(new Box($w, $h));

        $this->doResize($fit);
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
        if (100.0 === (float)$percent) {
            return;
        }

        $this->processed = true;

        $this->doResize($this->image->getSize()->scale($percent / 100));
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
        $px = (new Size($size->getWidth(), $size->getHeight()))->pixel($pixel);

        $this->targetSize = [$px->getWidth(), $px->getHeight()];

        $this->doResize($px);
    }

    /**
     * {@inheritdoc}
     */
    protected function unload()
    {
        if (null !== $this->image) {
            $this->image->destroy();
            $this->image = null;
        }

        parent::unload();
    }

    /**
     * doResize
     *
     * @param BoxInterface $size
     *
     * @return void
     */
    private function doResize(BoxInterface $size)
    {
        if (1 < count($layers = $this->image->layers())) {
            try {
                $layers->coalesce();
            } catch (\Exception $e) {
            }

            foreach ($layers as $frame) {
                $frame->resize($size);
            }

            $layers->merge();
        } else {
            $this->image->resize($size);
        }
    }

    /**
     * createMask
     *
     * @param BoxInterface $size
     * @param BoxInterface $image
     * @param PointInterface $point
     *
     * @return void
     */
    private function createMask(BoxInterface $size, BoxInterface $image, PointInterface $point)
    {
        $white = $this->image->palette()->color([255, 255, 255]);
        $black = $this->image->palette()->color([0, 0, 0]);

        $mask  = $this->imagine->create($size, $black);
        $fill  = $this->imagine->create($image, $white);

        $mask->paste($fill, $point);
        $mask->strip();

        return $mask;
    }

    /**
     * doCrop
     *
     * @param PointInterface $point
     * @param BoxInterface $target
     *
     * @return void
     */
    private function doCrop(PointInterface $point, BoxInterface $target)
    {
        if (1 < count($layers = $this->image->layers())) {
            try {
                $layers->coalesce();
            } catch (\Exception $e) {
            }

            foreach ($layers as $frame) {
                $frame->crop($point, $target);
            }

            return;
        }

        $this->image->crop($point, $target);
    }

    /**
     * createCanvas
     *
     * @param BoxInterface $size
     * @param ColorInterface $color
     *
     * @return ImageInterface
     */
    private function createCanvas(BoxInterface $size, ColorInterface $color)
    {
        $canvas = $this->imagine->create($size, $color);
        $canvas->usePalette($this->image->palette());
        $canvas->strip();

        return $canvas;
    }

    /**
     * extent
     *
     * @param BoxInterface $size
     * @param BoxInterface $target
     * @param PointInterface $point
     * @param ColorInterface $color
     *
     * @return void
     */
    private function extent(
        BoxInterface $size,
        BoxInterface $target,
        PointInterface $point,
        ColorInterface $color = null
    ) {
        $transp = $this->image->palette()->color([255, 255, 255]);
        $c = $transp->dissolve(-100);
        $canvas = $this->createCanvas($target, $c);

        if ($canvas instanceof \Imagine\Imagick\Image) {
            $canvas->getImagick()->setImageFormat($this->image->getImagick()->getImageFormat());
        } elseif ($canvas instanceof \Imagine\Gmagick\Image) {
            $canvas->getGmagick()->setImageFormat($this->image->getGmagick()->getImageFormat());
        }

        $canvas = $this->doExtent($this->image, $canvas, $size, $target, $point, $color);

        if (1 < $count = count($layers = $this->image->layers())) {
            try {
                $layers->coalesce();
            } catch (\Exception $e) {
            }

            $cl = $canvas->layers();
            $copy = $canvas->copy();

            foreach ($layers as $index => $layer) {
                $fm = $this->doExtent($layer, $copy->copy(), $size, $target, $point, $color);
                $cl->add($fm);
            }
        }

        $this->image = $canvas;
    }

    /**
     * doExtent
     *
     * @param mixed $image
     * @param mixed $canvas
     * @param BoxInterface $size
     * @param BoxInterface $target
     * @param PointInterface $point
     * @param ColorInterface $color
     *
     * @return ImageInterface
     */
    private function doExtent(
        ImageInterface $image,
        ImageInterface $canvas,
        BoxInterface $size,
        BoxInterface $target,
        PointInterface $point,
        ColorInterface $color = null
    ) {
        if ($size->getHeight() > $target->getHeight()) {
            $image->crop(new Point(0, $point->getY()), new Box($size->getWidth(), $target->getHeight()));
            $size = $this->image->getSize();
            $point = new Point($point->getX(), 0);
        } elseif ($size->getWidth() > $target->getWidth()) {
            $image->crop(new Point($point->getX(), 0), new Box($target->getWidth(), $size->getHeight()));
            $size = $this->image->getSize();
            $point = new Point(0, $point->getY());
        }

        if (null !== $color) {
            $frame = $this->createCanvas($target, $color);
            $mask = $this->createMask($target, $size, $point);
            $frame->applyMask($mask, $point);
            $canvas = $frame;
            unset($mask);
        }

        $canvas->paste($image, $point);

        return $canvas;
    }
}
