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
    use Scaling;

    private $filters;
    private $image;
    private $imagine;
    private $resource;
    private $processed;
    protected $targetFormat;
    protected $targetSize;
    protected $options;

    /**
     * Constructor.
     *
     * @param ImagineInterface $imagine
     * @param FilterResolverInterface $filters
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
     * {@inheritdoc}
     */
    public function setCurrentImage(ImageInterface $image)
    {
        $this->image = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentImage()
    {
        return $this->image;
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

        $ratio = $this->getRatio();

        if (0 === max($h, $w)) {
            throw new \InvalidArgumentException();
        } elseif (0 === $w) {
            $w = round($h * $ratio);
        } elseif (0 === $h) {
            $h = round($w / $ratio);
        }

        $this->doResize(new Box($w, $h));
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

        $gravity = new Gravity($gravity);
        $target = new Box($w, $h);
        $size = $this->image->getSize();

        if (!$size->contains($target)) {
            $color = null !== $background ? $this->image->palette()->color($background) : null;
            $image = $this->imagine->create($target, $color);
            $image->usePalette($this->image->palette());
            $image->strip();

            if (($size->getWidth() < $target->getWidth() && $size->getHeight() > $target->getHeight()) ||
                ($size->getHeight() < $target->getHeight() && $size->getWidth() > $target->getWidth())) {
                $this->doCrop(new Point(0, 0), $target);
                $size = $this->image->getSize();
                $point = $gravity->getPoint($target, $size);
            } else {
                $point = $gravity->getPoint($target, $size);
            }

            if (1 < $this->image->layers()->count()) {
                $this->doPaste($image, $point);
            } else {
                $image->paste($this->image, $point);
            }

            $this->image = $image;
        } else {
            $point = $gravity->getPoint($size, $target);
            $this->doCrop($point, $target);
        }
    }

    /**
     * doPaste
     *
     * @param ImageInterface $image
     * @param mixed $layers
     * @param PointInterface $point
     *
     * @return void
     */
    protected function doPaste(ImageInterface &$image, PointInterface $point)
    {
        $palette = $image->palette();
        $meta    = $image->metadata();

        $w = $image->getSize()->getWidth();
        $h = $image->getSize()->getHeight();

        if ($image instanceof \Imagine\Imagick\Image) {

            $im = $image->getImagick();
            $bk = $im->getImageBackgroundColor();
            $frames = $this->image->getImagick()->coalesceImages();
            $fmt = $frames->getImageFormat();
            $im->setFirstIterator();
            $im->removeImage();

            $frames->setFirstIterator();

            do {
                $im->newImage($w, $h, $bk, $fmt);
                $im->setIteratorIndex($frames->getIteratorIndex());
                $im->compositeimage($frames->getImage(), \Imagick::COMPOSITE_DEFAULT, $point->getX(), $point->getY());
            } while ($frames->nextImage());

            $im->setImageFormat($fmt);

        } elseif ($image instanceof \Imagine\Gmagick\Image && method_exists($image->getGmgick(), 'coalesceImages')) {

            $gm = $image->getGmagick();
            $bk = $gm->getImageBackgroundColor()->getColor(false);
            $frames = $this->image->getGmagick()->coalesceImages();
            $fmt = $frames->getImageFormat();
            $frames->setImageIndex(0);
            $gm->setImageIndex(0);
            $gm->removeImage();

            do {
                $gm->newImage($w, $h, $bk, $fmt);
                $gm->setImageIndex($frames->getImageIndex());
                $gm->compositeimage($frames->getImage(), \Gmagick::COMPOSITE_DEFAULT, $point->getX(), $point->getY());
            } while ($frames->nextImage());

            $gm->setImageFormat($fmt);
        } else {
            $image->copy($this->image, $point);
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

        $size = $this->image->getSize();

        $this->fillArea($w, $h, $ow = $size->getWidth(), $oh = $size->getHeight());

        $this->doResize(new Box($w, $h));

        $this->crop($gravity);
    }

    /**
     * doCrop
     *
     * @param mixed $point
     * @param mixed $target
     *
     * @return void
     */
    protected function doCrop(PointInterface $point, BoxInterface $target)
    {
        if ($this->image instanceof \Imagine\Imagick\Image && 1 < $this->image->getImagick()->getNumberImages()) {

            $im = $this->image->getImagick()->coalesceImages();
            $im->setFirstIterator();

            do {
              $im->cropImage($target->getWidth(), $target->getHeight(), $point->getX(), $point->getY());
              $im->setImagePage(0, 0, 0, 0);
            } while ($im->nextImage());

            $class = get_class($this->image);
            $this->image = new $class($im->deconstructImages(), $this->image->palette(), $this->image->metadata());

        } elseif ($this->image instanceof \Imagine\Gmagick\Image && 1 < $this->image->getGmagick()->getNumberImages()) {

            $gm = $this->image->getGmagick();

            if (!method_exists($gm, 'coalesceImages')) {

                foreach ($this->layers as $layer) {
                    $layer->crop($point, $target);
                }

                return;
            }

            $gm = $gm->coalesceImages();
            $gm->setImageIndex(0);

            do {
                try {
                    $gm->cropImage($target->getWidth(), $target->getHeight(), $point->getX(), $point->getY());
                    $gm->setImagePage(0, 0, 0, 0);
                } catch (\GmagickException $e) {
                    // strip frame?
                    $gm->removeImage();
                }
            } while ($gm->nextImage());

            $class = get_class($this->image);
            $this->image = new $class($gm->deconstructImages(), $this->image->palette(), $this->image->metadata());

        } else {
            $this->image->crop($point, $target);
        }
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
        if ($this->image instanceof \Imagine\Imagick\Image && 1 < $this->image->getImagick()->getNumberImages()) {

            $this->image->layers()->coalesce();

            foreach ($this->image->layers() as $frame) {
                $frame->resize($size);
            }

        } elseif ($this->image instanceof \Imagine\Gmagick\Image && 1 < $this->image->getGmagick()->getNumberImages()) {

            $gm = $this->image->getGmagick();

            if (!method_exists($gm, 'coalesceImages')) {
                foreach ($this->layers as $layer) {
                    $layer->resize($size);
                }

                return;
            }

            $gm->setImageIndex(0);

            do {
                $this->image->resize($size);
            } while ($gm->nextImage());

            $class = get_class($this->image);
            $this->image = new $class($gm->coalesceImages(), $this->image->palette(), $this->image->metadata());
        } else {
            $this->image->resize($size);
        }
    }

    protected function coalesceGmagick(\Gmagick $gm)
    {
        if (!method_exists($gm, 'coalesceImages')) {
            return;
        }

        $meta = $this->image->metadata();
        $palette = $this->image->palette();
        $coalesce = $gm->coalesceImages();

        $class = get_class($this->image);
        $this->image = new $class($coalesce, $this->image->palette(), $this->image->metadata());
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

        list ($nw, $nh) = $this->fitInBounds($w, $h, $size->getWidth(), $size->getHeight());

        $this->image->resize(new Box($nw, $nh));
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

        $this->image->resize($this->image->getSize()->scale($percent / 100));
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
        list ($w, $h) = $this->pixelLimit($size->getWidth(), $size->getHeight(), (int)$pixel);

        $this->image->resize(new Box($w, $h));
    }
}
