<?php

/**
 * This File is part of the Thapp\JitImage\Driver package
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
 * Class: ImagickDriver
 *
 * @implements DriverInterface
 *
 * @package
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
    public function __construct()
    {
        $this->tmp  = sys_get_temp_dir();
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
        if (false === ($resource instanceof \Imagick)) {
            throw new \InvalidArgumentException('Wrong resource type');
        }

        return $this->resource;
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
        if (preg_match('/(png|gif|webp|jpe?g|tiff)/i', $type)) {
            $this->resource->setImageFormat($type);
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
        return $this->getMimeFromFormatString($this->resource->getImageFormat());
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
        $this->source = $source;
        $this->resource = new Imagick($source);
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return void
     */
    public function filter($name, $options)
    {
        $result = static::INT_FILTER;

        foreach ($this->resource as $frame) {
            $result = $this->callParentFilter($name, $options);
        }

        if (static::EXT_FILTER === $result) {
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
        if ($this->isMultipartImage()) {

            $this->tmpFile = tempnam($this->tmp, 'jitim_');
            $this->resource->writeImages($this->tmpFile, true);

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
        return null;
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
     * createCanvas
     *
     * @param mixed $width
     * @param mixed $height
     * @param string $background
     * @access protected
     * @return \Imagick
     */
    protected function createCanvas($width, $height, $background = '#000')
    {
        $canvas = new \Imagick();

        $canvas->newPseudoImage($width, $height, "canvas:$background");
        $canvas->setImageFormat($this->resource->getImageFormat());

        if (array_key_exists('icc', $this->resource->getImageProfiles())) {
            $canvas->setImageProfile('icc', $this->resource->getImageProfile('icc'));
        }

        return $canvas;
    }

    /**
     * isWithoutBounds
     *
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return boolean
     */
    protected function isWithoutBounds($width, $height)
    {
        return ($this->resource->getImageHeight() < $height) or ($this->resource->getImageWidth() < $width);
    }

    /**
     * composite
     *
     * @param \Imagick $image
     * @access protected
     * @return void
     */
    protected function composite(Imagick $image, $x = 0, $y = 0, $copy = Imagick::COMPOSITE_ATOP, $flip = true)
    {
        $image->compositeImage($this->resource, $copy, $x, $y);

        if (false !== $flip) {
            $this->resource->destroy();
            $this->resource = $image;
        }

        return $image;
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
        extract($this->getCropCoordinates(
            $this->resource->getImageWidth(), $this->resource->getImageHeight(),
            $width, $height, $this->resource->getGravity())
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
            if (0 === min($width, $height)) {
                //var_dump($width, $height); die;
            }
            break;
        // No scaling for larger images.
        // Would be easier to just set `bestfit`, but its behaviour changed
        // with imagemagick 3.0, so we have to calculate the best fit ou selfs.
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
            'type'   => $this->getOutputType(),
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
     * getMimeFromFormatString
     *
     * @param mixed $format
     * @access private
     * @return mixed
     */
    private function getMimeFromFormatString($format)
    {
        return sprintf('image/%s', strtolower($format));
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
