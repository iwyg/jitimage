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
use Imagine\Image\BoxInterface;
use Imagine\Image\PointInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
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
class Processor implements ProcessorInterface
{
    use Scaling;

    private $filters;
    private $image;
    private $imagine;
    private $options;
    private $resource;
    private $processed;
    private $quality;
    private $targetFormat;
    private $targetSize;

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

    public function setOptions(array $options)
    {
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
    public function close()
    {
        $this->targetFormat = null;
        $this->targetSize = null;
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
     * {@inheritdoc}
     */
    public function process(array $parameters)
    {
        $params = array_merge($this->defaultParams(), $parameters);

        $this->targetSize = [$params['width'], $params['height']];

        switch($params['mode']) {
            case static::IM_NOSCALE:
                break;
            case static::IM_RESIZE:
                $this->resize();
                break;
            case static::IM_SCALECROP:
                $this->cropScale($params['gravity']);
                break;
            case static::IM_CROP:
                $this->crop($params['gravity'], $params['background']);
                break;
            case static::IM_RSIZEFIT:
                $this->resizeToFit();
                break;
            case static::IM_RSIZEPERCENT:
                $this->resizePercentual($params['width']);
                break;
            case static::IM_RSIZEPXCOUNT:
                $this->resizePixelCount($params['width']);
                break;
        }

        foreach ((array)$params['filter'] as $f => $parameter) {
            $this->addFilter($f, (array)$parameter);
        }
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
     * set the image compression quality.
     *
     * This typically is a value between
     * 0 and 100
     *
     * @param int $quality
     *
     * @return void
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * set the output image format
     *
     * @param string $format
     *
     * @return void
     */
    public function setFileFormat($format)
    {
        $this->targetFormat = strtolower($format);
    }

    /**
     * get the image output format
     *
     * @return string
     */
    public function getFileFormat()
    {
        if (null === $this->targetFormat) {
            $this->targetFormat = $this->getSourceFormat();
        }

        return $this->targetFormat;
    }

    /**
     * getSourceFormat
     *
     * @return string
     */
    public function getSourceFormat()
    {
        if (null === $this->resource) {
            return;
        }

        $formats = array_flip(static::formats());

        if (isset($formats[$mime = $this->resource->getMimeType()])) {
            return $formats[$mime];
        }
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
     * getSourceMimeTime
     *
     *
     * @return string
     */
    public function getSourceMimeType()
    {
        return $this->resource->getMimeType();
    }

    /**
     * get the image output MimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->translateFormatToMime($this->getFileFormat());
    }

    /**
     * get the image input source path
     *
     * @return string
     */
    public function getSource()
    {
        return $this->resource->getPath();
    }

    /**
     * Determine if the image has been processed yet.
     *
     * @return bool
     */
    public function isProcessed()
    {
        return (bool)$this->processed;
    }

    /**
     * getLastModTime
     *
     * @return integet
     */
    public function getLastModTime()
    {
        if ($this->processed) {
            return time();
        }

        return $this->resource->getLastModified();
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

        $this->doResize($nb = new Box($w, $h));
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
            $point = $gravity->getPoint($target, $size);
            $image->paste($this->image, $point);

            $this->image = $image;
        } else {
            $point = $gravity->getPoint($size, $target);
            $this->doCrop($point, $target);
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
            $im = $this->image->getImagick();
            $index = $im->getIteratorIndex();
            $im->rewind();
            do {
                $this->image->crop($point, $target);
            } while ($im->nextImage());
            $im->setIteratorIndex($index);
        } elseif ($this->image instanceof \Imagine\Gmagick\Image && 1 < $this->image->getGmagick()->getNumberImages()) {
            $gm = $this->image->getGmagick();
            $index = $gm->getImageIndex();
            $gm->setImageIndex(0);
            do {
                $this->image->crop($point, $target);
            } while ($gm->nextImage());
            $gm->setImageIndex($index);
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
            $im = $this->image->getImagick();
            $index = $im->getIteratorIndex();
            $im->rewind();
            do {
                $this->image->resize($size);
            } while ($im->nextImage());
            $im->setIteratorIndex($index);

        } elseif ($this->image instanceof \Imagine\Gmagick\Image && 1 < $this->image->getGmagick()->getNumberImages()) {
            $gm = $this->image->getGmagick();
            $index = $gm->getImageIndex();
            $gm->setImageIndex(0);
            do {
                $this->image->resize($size);
            } while ($gm->nextImage());
            $gm->setImageIndex($index);
        } else {
            $this->image->resize($size);
        }
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

    /**
     * addFilter
     *
     * @param string $filter
     * @param array $options
     *
     * @return void
     */
    protected function addFilter($filter, array $options = [])
    {
        if (null === $this->filters) {
            return;
        }

        if ($filters = $this->filters->resolve($filter)) {
            foreach ($filters as $filter) {
                if ($filter->supports($this)) {
                    $filter->apply($this, $options);
                    break;
                }

                throw new \RuntimeException('No suitable Filter found');
            }
        } else {
            throw new \RuntimeException('Filter "'.$filter.'" not found.' );
        }
    }

    /**
     * defaultParams
     *
     * @return array
     */
    protected function defaultParams()
    {
        return [
            'mode'       => 0,
            'width'      => 100,
            'height'     => 100,
            'gravity'    => 0,
            'quality'    => 80,
            'background' => null,
            'filter'     => []
        ];
    }

    /**
     * translateFormatToMime
     *
     * @param string $format
     *
     * @return string
     */
    protected function translateFormatToMime($format)
    {
        $formats = static::formats();

        if (array_key_exists($format, $formats)) {
            return $formats[$format];
        }
    }

    /**
     * formats
     *
     * @return array
     */
    protected static function formats()
    {
        return [
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'tif'   => 'image/tiff',
            'tiff'  => 'image/tiff',
            'wbmp'  => 'image/vnd.wap.wbmp',
            'xbm'   => 'image/xbm',
        ];
    }
}
