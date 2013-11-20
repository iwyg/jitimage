<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Driver;

use \Imagick;
use \ImagickPixel;

/**
 * GD Processing Driver
 *
 * @implements DriverInterface
 * @uses Scaling
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImagickDriver extends ImDriver
{

    use Scaling;

    /**
     * driverType
     *
     * @var string
     */
    protected static $driverType = 'imagick';

    /**
     * resource
     *
     * @var mixed
     */
    protected $resource;

    /**
     * __construct
     *
     * @param BinLocatorInterface $locator
     * @access public
     */
    public function __construct(SourceLoaderInterface $loader)
    {
        $this->tmp  = sys_get_temp_dir();
        $this->loader = $loader;
    }

    /**
     * __destruct
     *
     * @access public
     * @return mixed
     */
    public function __destruct()
    {
        parent::__destruct();

        if (is_resource($this->resource)) {
            $this->resource->destroy();
        }
    }

    /**
     * getImageResource
     *
     * @access public
     * @return mixed
     */
    public function &getResource()
    {
        return $this->resource;
    }

    /**
     * swapResource
     *
     * @param mixed $resource
     * @access public
     * @return mixed
     */
    public function swapResource($resource)
    {
        if (false === ($resource instanceof Imagick)) {
            throw new \InvalidArgumentException('Wrong resource type');
        }

        $this->resource = $resource;
    }

    /**
     * setOutputType
     *
     * @param mixed $type
     * @access public
     * @return mixed
     */
    public function setOutputType($type)
    {
        if (preg_match('/(png|gif|jpe?g|tif?f|webp)/i', $type)) {
            $this->resource->setImageFormat($type);
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid output format %s', $type));
        }
    }

    /**
     * getOutputType
     *
     * @access public
     * @return mixed
     */
    public function getOutputType()
    {
        return $this->formatType($this->resource->getImageFormat());
    }


    /**
     * load
     *
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function load($source)
    {
        $this->clean();

        if ($src = $this->loader->load($source)) {
            $this->source = $src;
            $this->resource = new Imagick($source);
            $this->getInfo();

            return true;
        }

        $this->error = 'error loading source';
        return false;
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return void
     */
    public function filter($name, array $options = [])
    {
        $result = static::INT_FILTER;

        if ($this->isMultipartImage()) {
            $this->resource = $this->resource->coalesceImages();
        }

        foreach ($this->resource as $frame) {
            $result = $this->callParentFilter($name, $options);
        }


        if (static::EXT_FILTER === $result and isset($this->filters[$name])) {

            $filter = new $this->filters[$name]($this, $options);

            foreach ($this->resource as $frame) {
                $filter->run();
            }
        }

    }

    /**
     * getImageBlob
     *
     * @access public
     * @return string
     */
    public function getImageBlob()
    {
        if (!$this->processed) {
            return file_get_contents($this->source);
        }

        if ($this->isMultipartImage()) {

            $this->tmpFile = tempnam($this->tmp, 'jitim_');

            $image = $this->resource->deconstructImages();
            $image->writeImages($this->tmpFile, true);

            $image->clear();
            $image->destroy();

            return file_get_contents($this->tmpFile);

        }

        return $this->resource->getImageBlob();
    }

    /**
     * setBackgroundColor
     *
     * @param mixed $color
     * @access public
     * @return mixed
     */
    public function setBackgroundColor($color)
    {
        $this->resource->setImageBackgroundColor($color);
    }

    /**
     * setQuality
     *
     * @param mixed $param
     * @access public
     * @return mixed
     */
    public function setQuality($quality)
    {
        $this->resource->setImageCompressionQuality($quality);
    }

    /**
     * process
     *
     * @param mixed $param
     * @access public
     * @return void
     */
    public function process()
    {
        $this->processed = true;
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        if ($this->resource instanceof Imagick) {
            $this->resource->destroy();
        }

        parent::clean();
    }

    /**
     * filterResizeToFit
     *
     * @access protected
     * @return void
     */
    protected function filterResizeToFit()
    {
        $this->resize($this->targetSize['width'], $this->targetSize['height'], static::FL_OSRK_LGR);
    }

    /**
     * gravity
     *
     * pretty useless compared to the cli version
     *
     * @param mixed $gravity
     * @param string $flag
     * @access protected
     * @return \Thapp\JitImage\Driver\ImagickDriver
     */
    protected function gravity($gravity, $flag = '')
    {
        $this->resource->setGravity($gravity);
        return $this;
    }

    /**
     * repage
     *
     * @access protected
     * @return mixed
     */
    protected function repage()
    {
        $this->resource->setImagePage(0, 0, 0, 0);
    }

    /**
     * background
     *
     * @param mixed $color
     * @access protected
     * @return \Thapp\JitImage\Driver\ImagickDriver
     */
    protected function background($color = null)
    {
        if (!is_null($color)) {
            $this->resource->setImageBackgroundColor(sprintf('#%s', $color));
        }
        return $this;
    }
    /**
     * extent
     *
     * @param mixed $width
     * @param mixed $height
     * @param string $flag
     * @access protected
     * @return \Thapp\JitImage\Driver\ImagickDriver
     */
    protected function extent($width, $height, $flag = '')
    {
        extract(
            $this->getCropCoordinates(
                $this->resource->getImageWidth(),
                $this->resource->getImageHeight(),
                $width,
                $height,
                $this->resource->getGravity()
            )
        );

        $this->resource->extentImage($width, $height, $x, $y);
        return $this;
    }

    /**
     * resize
     *
     * @param mixed  $width
     * @param mixed  $height
     * @param string $flag
     * @access protected
     * @return \Thapp\JitImage\Driver\ImagickDriver
     */
    protected function resize($width, $height, $flag = '')
    {

        switch ($flag) {
            // oversize image to fill the boudaries
            case static::FL_FILL_AREA:
                $this->fillArea($width, $height, $this->getInfo('width'), $this->getInfo('height'));
                break;
            // ignoring aspect ration is default behaviour on imagick resize
            case static::FL_IGNR_ASPR:
                break;
            case static::FL_PIXL_CLMT:
                extract($this->pixelLimit($this->getInfo('width'), $this->getInfo('height'), $width));
                break;
            case static::FL_RESZ_PERC:
                extract($this->percentualScale($this->getInfo('width'), $this->getInfo('height'), $width));
                break;
            // No scaling for larger images.
            // Would be easier to just set `bestfit`, but its behaviour changed
            // with imagemagick 3.0, so we have to calculate the best fit our selfs.
            case static::FL_OSRK_LGR:
                extract($this->fitInBounds($width, $height, $this->getInfo('width'), $this->getInfo('height')));
                break;
            // therefore we set $height always to zero
            default:
                $height = 0;
                break;
        }

        // filter and blur differ for up and downscaling
        if ($width > $this->getInfo('width') or $height > $this->getInfo('height')) {
            $filter = Imagick::FILTER_CUBIC;
            $blur   = 0.6;
        } else {
            $filter = Imagick::FILTER_SINC;
            $blur   = 1;
        }

        $this->resource->resizeImage($width, $height, $filter, $blur);
        return $this;
    }



    /**
     * getSourceAttributes
     *
     * @access protected
     * @return array
     */
    protected function getSourceAttributes()
    {
        extract($this->resource->getImageGeometry());

        return [
            'width'  => $width,
            'height' => $height,
            'ratio'  => $this->ratio($width, $height),
            'size'   => $this->resource->getImageLength(),
            'type'   => sprintf('image/%s', strtolower($this->resource->getImageFormat())),
        ];
    }

    /**
     * isMultipartImage
     *
     * @access protected
     * @return mixed
     */
    protected function isMultipartImage()
    {
        return $this->resource->getNumberImages() > 1;
    }


    /**
     * callParentFilter
     *
     * @access private
     * @return void
     */
    private function callParentFilter()
    {
        return call_user_func_array([$this, 'Thapp\JitImage\Driver\AbstractDriver::filter'], func_get_args());
    }
}
