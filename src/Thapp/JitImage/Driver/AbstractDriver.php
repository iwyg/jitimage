<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Driver package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Driver;

/**
 * Class: AbstractDriver
 *
 * @implements DriverInterface
 * @abstract
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
abstract class AbstractDriver implements DriverInterface
{

    /**
     * @var string
     */
    const FL_IGNR_ASPR = '!';

    /**
     * @var string
     */
    const FL_FILL_AREA = '^';

    /**
     * @var string
     */
    const FL_RESZ_PERC = '%';

    /**
     * @var string
     */
    const FL_PIXL_CLMT = '@';

    /**
     * @var string
     */
    const FL_OENL_SML  = '<';

    /**
     * @var string
     */
    const FL_OSRK_LGR  = '>';

    /**
     * @var int
     */
    const INT_FILTER = 0;

    /**
     * @var int
     */
    const EXT_FILTER = 1;

    /**
     * filters
     *
     * @var array
     */
    protected $filters = [];

    /**
     * targetSize
     *
     * @var array
     */
    protected $targetSize = [];

    /**
     * sourceAttributes
     *
     * @var array
     */
    protected $sourceAttributes;

    /**
     * outputType
     *
     * @var mixed
     */
    protected $outputType;

    /**
     * clean up temporary files after shutdown
     *
     * @access public
     * @return mixed
     */
    public function __destruct()
    {
        return $this->clean();
    }

    /**
     * registerFilter
     *
     * @param mixed $alias
     * @param mixed $class
     * @access public
     * @return mixed
     */
    public function registerFilter($alias, $class)
    {
        $this->filters[$alias] = $class;
    }

    /**
     * getDriverType
     *
     * @access public
     * @final
     * @return string
     */
    final public function getDriverType()
    {
        return static::$driverType;
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return mixed
     */
    public function filter($name, $options)
    {
        if (method_exists($this, $filter = 'filter' . ucfirst($name))) {
            call_user_func_array([$this, $filter], is_array($options) ? $options : []);
            return static::INT_FILTER;
        }

        return static::EXT_FILTER;
    }

    /**
     * setTargetSize
     *
     * @param mixed $width
     * @param mixed $height
     * @access public
     * @return void
     */
    public function setTargetSize($width, $height)
    {
        $this->targetSize = compact('width', 'height');
    }

    /**
     * getTargetSize
     *
     * @access public
     * @return array
     */
    public function getTargetSize()
    {
        extract($this->targetSize);
        return $this->getImageSize($width, $height);
    }

    /**
     * getInfo
     *
     * @param mixed $attribute
     * @access public
     * @return mixed
     */
    public function getInfo($attribute = null)
    {
        if (!isset($this->sourceAttributes)) {
            $this->sourceAttributes = $this->getSourceAttributes();
        }

        if (!is_null($attribute)) {
            return isset($this->sourceAttributes[$attribute]) ? $this->sourceAttributes[$attribute] : null;
        }

        return $this->sourceAttributes;
    }

    /**
     * setOutputType
     *
     * @param mixed $type
     * @access public
     * @return void
     */
    public function setOutputType($type)
    {
        if (preg_match('/(png|gif|webp|jpe?g|tiff)/i', $type)) {
            $this->outputType = sprintf('image/%s', strtolower($type));
        }
    }

    /**
     * getOutputType
     *
     * @access public
     * @return string
     */
    public function getOutputType()
    {
        $type = $this->outputType;

        if (is_null($type)) {
            $type = $this->getInfo('type');
        }

        return preg_replace('~image/~', null, $this->formatType($type));
    }

    protected function formatType($type)
    {
        return strtolower(preg_replace('~jpg~', 'jpeg', $type));
    }

    /**
     * getOutputMimeType
     *
     * @access public
     * @return mixed
     */
    public function getOutputMimeType()
    {
        return $this->getMimeFromFormatString($this->getOutputType());
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
     * filterResize
     *
     * @param mixed $param
     *
     * @access protected
     * @return void
     */
    protected function filterResize()
    {
        $this->resize($this->targetSize['width'], $this->targetSize['height'], static::FL_IGNR_ASPR);
    }

    /**
     * filterCropScale
     *
     * @param mixed $width
     * @param mixed $height
     * @param mixed $gravity
     * @access protected
     * @return void
     */
    protected function filterCropScale($gravity)
    {
        $this
            ->resize($this->targetSize['width'], $this->targetSize['height'], static::FL_FILL_AREA)
            ->gravity($gravity)
            ->extent($this->targetSize['width'], $this->targetSize['height']);
    }

    /**
     * filterCrop
     *
     * @param mixed $with
     * @param mixed $height
     * @param mixed $gravity
     *
     * @access protected
     * @return void
     */
    protected function filterCrop($gravity, $background = null)
    {
        $this
            ->background($background)
            ->gravity($gravity)
            ->extent($this->targetSize['width'], $this->targetSize['height']);
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
     * resize
     *
     * @param mixed $width
     * @param mixed $height
     * @param string $flag
     * @access protected
     * @abstract
     * @return mixed
     */
    abstract protected function resize($width, $height, $flag = '');

    /**
     * gravity
     *
     * @param mixed $gravity
     * @param string $flag
     * @access protected
     * @abstract
     * @return mixed
     */
    abstract protected function gravity($gravity, $flag = '');

    /**
     * extent
     *
     * @param mixed $width
     * @param mixed $height
     * @param string $flag
     * @access protected
     * @abstract
     * @return mixed
     */
    abstract protected function extent($width, $height, $flag = '');

    /**
     * background
     *
     * @param mixed $color
     * @access protected
     * @abstract
     * @return mixed
     */
    abstract protected function background($color = null);

    /**
     * getFilesizeFromCommand
     *
     * @param mixed $width
     * @param mixed $height
     * @access private
     * @return array
     */
    protected function getImageSize($width, $height)
    {
        $min = min($width, $height);

        // if one value is zero, we have to calculate the right
        // value using the image aspect raion
        if (0 === $min) {

            // if both hight and widh are zero we assume
            // that the image is not resized at all
            if (0 === max($width, $height)) {
                extract($this->getInfo());
            } else {
                $ratio = $this->getInfo('ratio');
            }

            $width  = $width  === 0 ? (int)floor($height * $ratio) : $width;
            $height = $height === 0 ? (int)floor($width  / $ratio) : $height;
        }
        return compact('width', 'height');
    }

    /**
     * getSourceAttributes
     *
     * @access protected
     * @return array
     */
    protected function getSourceAttributes()
    {
        $info = getimagesize($this->source);

        list($width, $height) = $info;

        return [
            'width'    => $width,
            'height'   => $height,
            'ratio'    => $this->ratio($width, $height),
            'size'     => filesize($this->source),
            'type'     => $info['mime']
        ];
    }
}
