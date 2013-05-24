<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

use Thapp\JitImage\Driver\DriverInterface;

/**
 * @class ImagickFilter
 */

abstract class AbstractFilter implements FilterInterface
{
    /**
     * driver
     *
     * @var mixed
     */
    protected $driver = [];

    /**
     * options
     *
     * @var array
     */
    protected $options = [];

    /**
     * run
     *
     * @access public
     * @abstract
     * @return void
     */
    abstract public function run();

    /**
     * __construct
     *
     * @param Imagick $resource
     * @access public
     * @return mixed
     */
    final public function __construct(DriverInterface $driver, $options)
    {
        $this->driver  = $driver;
        $this->options = $options;

        $this->ensureCompat();
    }

    /**
     * getOption
     *
     * @param mixed $option
     * @param mixed $default
     * @access public
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }
        return $default;
    }

    /**
     * ensureCompat
     *
     * @access private
     * @return void
     */
    private function ensureCompat()
    {
        if (!static::$driverType) {
            throw new \Exception(sprintf('trying to apply incopatible filter on %s driver', $this->driver->getDriverType()));
        }
    }
}
