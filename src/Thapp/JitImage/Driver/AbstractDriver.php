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

/**
 * Abstract processing driver
 *
 * @implements DriverInterface
 * @abstract
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
abstract class AbstractDriver implements DriverInterface
{

    /**
     * Ignore aspect ration flag
     *
     * @var string
     */
    const FL_IGNR_ASPR = '!';

    /**
     * Fill area flag
     *
     * @var string
     */
    const FL_FILL_AREA = '^';

    /**
     * procentual resize flag
     *
     * @var string
     */
    const FL_RESZ_PERC = '%';

    /**
     * pixel count limit flag
     *
     * @var string
     */
    const FL_PIXL_CLMT = '@';

    /**
     * only resize smaller flag
     *
     * @var string
     */
    const FL_OENL_SML  = '<';

    /**
     * only resize larger flag
     *
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
     * error
     *
     * @var string
     */
    protected $error;

    /**
     * processed
     *
     * @var bool
     */
    protected $processed = false;

    /**
     * clean up temporary files after shutdown
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->clean();
    }

    /**
     * register external filter.
     *
     * @param string $alias the filter alias
     * @param string $class full qualified filter classname
     *
     * @access public
     * @return void
     */
    public function registerFilter($alias, $class)
    {
        $this->filters[$alias] = $class;
    }

    /**
     * Determine if an image has been processed yet.
     *
     * @access public
     * @return bool
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $this->processed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->source           = null;
        $this->resource         = null;
        $this->processed        = false;
        $this->targetSize       = null;
        $this->outputType       = null;
        $this->sourceAttributes = null;
    }

    /**
     * Retrurns the driver type name.
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
     * Call a filter on the driver.
     *
     * if the filter method exists on the driver the method will be called,
     * otherwise it will return a flag to indecate that the filter is an
     * external one.
     *
     * @param string $name
     * @param array  $options
     * @access public
     * @return int
     */
    public function filter($name, array $options = [])
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
     * getError
     *
     * @access public
     * @return mixed
     */
    public function getError()
    {
        return !is_null($this->error) ? $this->error : false;
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
     * getSource
     *
     * @access public
     * @return mixed
     */
    public function getSource()
    {
        return $this->loader->getSource();
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
        if (preg_match('/(png|gif|jpe?g|tif?f|webp)/i', $type)) {
            $this->outputType = sprintf('image/%s', strtolower($type));
            return;
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid output format %s', $type));
        }
    }

    /**
     * getSourceFormat
     *
     * @param mixed $assSuffix
     *
     * @access public
     * @return string
     */
    public function getSourceType($assSuffix = false)
    {
        $type = $this->getInfo('type');
        return (bool)$assSuffix ? strtr(preg_replace('~image/~', null, $this->formatType($type)), ['jpeg' => 'jpg']) : $type;
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
        return image_type_to_mime_type($this->getImageTypeConstant($this->getOutputType()));
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
     * Crop and resize filter.
     *
     * @param int $width
     * @param int $height
     * @param int $gravity
     *
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
     * Crop filter.
     *
     * @param int $with
     * @param int $height
     * @param int $gravity
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
     * Best fit filter.
     *
     * @access protected
     * @return void
     */
    protected function filterResizeToFit()
    {
        $this->resize($this->targetSize['width'], $this->targetSize['height'], static::FL_OSRK_LGR);
    }

    /**
     * Percentual resize filter.
     *
     * @access protected
     * @return void
     */
    protected function filterPercentualScale()
    {
        $this->resize($this->targetSize['width'], 0, static::FL_RESZ_PERC);
    }

    /**
     * filterPercentualScale
     *
     * @access protected
     * @return void
     */
    protected function filterResizePixelCount()
    {
        $this->resize($this->targetSize['width'], 0, static::FL_PIXL_CLMT);
    }

    /**
     * Resize the image.
     *
     * @param int    $width
     * @param int    $height
     * @param string $flag
     *
     * @access protected
     * @abstract
     * @return void
     */
    abstract protected function resize($width, $height, $flag = '');

    /**
     * Set the image gravity.
     *
     * @param int    $gravity
     * @param string $flag
     *
     * @access protected
     * @abstract
     * @return void
     */
    abstract protected function gravity($gravity, $flag = '');

    /**
     * Extent the image.
     *
     * @param int    $width
     * @param int    $height
     * @param string $flag
     *
     * @access protected
     * @abstract
     * @return void
     */
    abstract protected function extent($width, $height, $flag = '');

    /**
     * Set the image background.
     *
     * @param string $color hex color representation.
     *
     * @access protected
     * @abstract
     * @return void
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

            $width  = $width  === 0 ? (int) floor($height * $ratio) : $width;
            $height = $height === 0 ? (int) floor($width  / $ratio) : $height;
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

    /**
     * getImageTypeConstant
     *
     * @param mixed $type
     * @access private
     * @return int
     */
    private function getImageTypeConstant($type)
    {
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                return IMAGETYPE_JPEG;
            case 'gif':
                return IMAGETYPE_GIF;
            case 'png':
                return IMAGETYPE_PNG;
            case 'webp':
                return IMAGETYPE_WBMP;
            case 'webp':
                return IMAGETYPE_WBMP;
            case 'ico':
                return IMAGETYPE_ICO;
            case 'bmp':
                return IMAGETYPE_BMP;
            default:
                return IMAGETYPE_JPC;
        }
    }
}
