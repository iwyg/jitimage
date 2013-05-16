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
     * driver
     *
     * @var \Thapp\JitImage\Driver\DriverInterface
     */
    protected $driver;

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
     * @return mixed
     */
    public function load($source)
    {
        return null;
    }


    /**
     * __get
     *
     * @param mixed $attribute
     * @access public
     * @return mixed
     */
    public function __get($attribute)
    {
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
    }
    /**
     * filter
     *
     * @access public
     */
    public function filter(FilterInterface $filter)
    {

    }

    /**
     * crop
     *
     * @param FilterInterface $filter
     * @access public
     * @return mixed
     */
    public function crop(FilterInterface $filter)
    {

    }

    /**
     * scale
     *
     * @param FilterInterface $filter
     * @access public
     * @return mixed
     */
    public function scale(FilterInterface $filter)
    {

    }

    /**
     * getType
     *
     * @param mixed $param
     * @access protected
     * @return mixed
     */
    protected function getType($param)
    {
        return $this->driver->getImageType($this);
    }

}
