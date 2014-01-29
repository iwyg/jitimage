<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use Thapp\JitImage\Driver\DriverInterface;

/**
 * Processingdriver proxy.
 *
 * @implements ImageInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class Image implements ImageInterface
{
    /**
     * @var int
     */
    const IM_NOSCALE      = 0;

    /**
     * @var int
     */
    const IM_RESIZE       = 1;

    /**
     * @var int
     */
    const IM_SCALECROP    = 2;

    /**
     * @var int
     */
    const IM_CROP         = 3;

    /**
     * @var int
     */
    const IM_RSIZEFIT     = 4;

    /**
     * @var int
     */
    const IM_RSIZEPERCENT = 5;

    /**
     * @var int
     */
    const IM_RSIZEPXCOUNT = 6;

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
     * create a new instance of Image
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
     * {@inheritDoc}
     */
    public function load($source)
    {
        return $this->driver->load($source);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ResolverInterface $resolver)
    {
        $params = $resolver->getParameter();

        $this->driver->setTargetSize($params['width'], $params['height']);

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
            default:
                break;
        }

        foreach ($params['filter'] as $f => $parameter) {
            $this->addFilter($f, $parameter);
        }

        $this->driver->setQuality($this->compression);
        $this->driver->process();
    }

    /**
     * {@inheritDoc}
     */
    public function setQuality($quality)
    {
        $this->compression = $quality;
    }

    /**
     * {@inheritDoc}
     */
    public function setFileFormat($format)
    {
        return $this->driver->setOutputType($format);
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        return $this->driver->getImageBlob();
    }

    /**
     * {@inheritDoc}
     */
    public function getFileFormat()
    {
        return $this->driver->getOutputType();
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceFormat()
    {
        return $this->driver->getSourceType(true);
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceMimeTime()
    {
        return $this->driver->getSourceType(false);
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType()
    {
        return $this->driver->getOutputMimeType();
    }

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->driver->getSource();
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return $this->driver->clean();
    }

    /**
     * isProcessed
     *
     * @access public
     * @return mixed
     */
    public function isProcessed()
    {
        return $this->driver->isProcessed();
    }

    /**
     * getLastModTime
     *
     * @access public
     * @return mixed
     */
    public function getLastModTime()
    {
        if ($this->isProcessed()) {
            return time();
        }

        return filemtime($this->driver->getSource());
    }

    /**
     * addFilter
     *
     * @access public
     * @return mixed
     */
    protected function addFilter($filter, array $options = [])
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
    protected function resize()
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
    protected function cropScale()
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
    protected function crop()
    {
        return $this->driver->filter('crop', func_get_args());
    }

    /**
     * mode 4 filter: resizeToFit
     *
     * @access public
     * @return void
     */
    protected function resizeToFit()
    {
        return $this->driver->filter('resizeToFit', func_get_args());
    }

    /**
     * mode 5 filte: percentualScale
     *
     * @access protected
     * @return void
     */
    protected function resizePercentual()
    {
        return $this->driver->filter('percentualScale', func_get_args());
    }

    /**
     * mode 6 filte: resizePixelCount
     *
     * @access protected
     * @return void
     */
    protected function resizePixelCount()
    {
        return $this->driver->filter('resizePixelCount', func_get_args());
    }
}
