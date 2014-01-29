<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

use Thapp\JitImage\Driver\DriverInterface;

/**
 * Class: AbstractFilter
 *
 * @implements FilterInterface
 * @abstract
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
abstract class AbstractFilter implements FilterInterface
{
    use ProcessingTrait;

    /**
     * driver
     *
     * @var mixed
     */
    protected $driver;

    /**
     * options
     *
     * @var array
     */
    protected $options;

    protected $availableOptions = [];

    /**
     * Exeecute the filter processing.
     *
     * @access public
     * @abstract
     * @return void
     */
    abstract public function run();

    /**
     * Creates a new filter object.
     *
     * @param Imagick $resource
     * @access public
     */
    final public function __construct(DriverInterface $driver, $options)
    {
        $this->driver  = $driver;
        $this->setOptions($options);

        $this->ensureCompat();
    }

    /**
     * setOptions
     *
     * @param array $options
     *
     * @access protected
     * @return void
     */
    protected function setOptions(array $options)
    {
        $this->options = [];

        foreach ($options as $option => $value) {

            if (!in_array($option, (array)$this->availableOptions)) {
                throw new \InvalidArgumentException(
                    sprintf('filter %s has no option "%s"', get_class($this), $option)
                );
            }

            $this->options[$option] = $value;
        }
    }

    /**
     * Get a filter option.
     *
     * @param string $option option name
     * @param mixed  $default the default value to return
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
     * Ensure driver compatibility.
     *
     * @throws \Exception
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
