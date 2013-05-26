<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use Thapp\JitImage\Driver\DriverInterface;

/**
 * Class: Image
 *
 * @implements ImageInterface
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class Image implements ImageInterface
{
    /**
     * @var int
     */
    const IM_NOSCALE    = 0;

    /**
     * @var int
     */
    const IM_RESIZE      = 1;

    /**
     * @var int
     */
    const IM_SCALECROP = 2;

    /**
     * @var int
     */
    const IM_CROP       = 3;

    /**
     * @var int
     */
    const IM_RSIZEFIT   = 4;

    /**
     * driver
     *
     * @var \Thapp\JitImage\Driver\DriverInterface
     */
    protected $driver;

    /**
     * compression
     *
     * @var int
     */
    protected $compression = 80;

    /**
     * attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * __construct
     *
     * @param InterfaceDriver $driver
     * @access public
     */
    public function __construct(DriverInterface $driver, $source = null)
    {
        $this->driver = $driver;

        if (!is_null($source)) {
            $this->load($source);
        }
    }

    /**
     * load
     *
     * @param string $source image source url
     * @access public
     * @return void
     */
    public function load($source)
    {
        return $this->driver->load($source);
    }

    /**
     * process
     *
     * @param ResolverInterface $resolver
     * @access public
     * @return void
     */
    public function process(ResolverInterface $resolver)
    {
        extract($resolver->getParameter());

        $this->driver->setTargetSize($width, $height);

        switch($mode) {

        case static::IM_NOSCALE:
            break;
        case static::IM_RESIZE:
            $this->resize();
            break;
        case static::IM_SCALECROP:
            $this->cropScale($gravity);
            break;
        case static::IM_CROP:
            $this->crop($gravity, $background);
            break;
        case static::IM_RSIZEFIT:
            $this->resizeToFit();
            break;
        default:
            break;
        }

        foreach ($filter as $f => $parameter) {
            $this->addFilter($f, $parameter);
        }

        $this->driver->setQuality($this->compression);
        $this->driver->process();
    }

    /**
     * setQuality
     *
     * @param int $quality compression quality 0 - 100
     * @access public
     * @return mixed
     */
    public function setQuality($quality)
    {
        $this->compression = $quality;
    }

    /**
     * getContents
     *
     * @access public
     * @return string
     */
    public function getContents()
    {
        return $this->driver->getImageBlob();
    }

    /**
     * getFileFormat
     *
     * @access public
     * @return mixed
     */
    public function getFileFormat()
    {
        return $this->driver->getOutputType();
    }

    /**
     * getMimeType
     *
     * @access public
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->driver->getOutputMimeType();
    }

    /**
     * setFileFormat
     *
     * @access public
     * @return void
     */
    public function setFileFormat($format)
    {
        return $this->driver->setOutputType($format);
    }

    /**
     * addFilter
     *
     * @access public
     * @return mixed
     */
    public function addFilter($filter, array $options = [])
    {
        $this->driver->filter($filter, $options);
    }

    /**
     * mode 1 filter: scale
     *
     * @param FilterInterface $filter
     * @access public
     * @return mixed
     */
    public function resize()
    {
        return $this->driver->filter('resize', func_get_args());
    }

    /**
     * mode 2 filter: cropScale
     *
     * @param mixed $width
     * @param mixed $height
     * @param mixed $gravity
     * @access public
     * @return mixed
     */
    public function cropScale($gravity)
    {
        return $this->driver->filter('cropScale', func_get_args());
    }

    /**
     * mode 3 filter: crop
     *
     * @param FilterInterface $filter
     * @access public
     * @return mixed
     */
    public function crop($gravity, $background = null)
    {
        return $this->driver->filter('crop', func_get_args());
    }

    /**
     * mode 4 filter: resizeToFit
     *
     * @access public
     * @return void
     */
    public function resizeToFit()
    {
        return $this->driver->filter('resizeToFit', func_get_args());
    }

    /**
     * close
     *
     * @access public
     * @return mixed
     */
    public function close()
    {
        return $this->driver->clean();
    }

    public function getSource()
    {
        return $this->driver->getSource();
    }
}
