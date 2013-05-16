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
class ImagickDriver implements DriverInterface
{
    /**
     * resource
     *
     * @var mixed
     */
    protected $resource;

    /**
     * load
     *
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function load($source)
    {
        $this->resource = new Imagick($source);
    }

    /**
     * getInfo
     *
     * @access public
     * @return mixed
     */
    public function getInfo()
    {
        extract($this->resource->getImageGometry());
        return [
            'width'  => $width,
            'height' => $height,
            'size'   => $this->resource->getImageSize(),
            'type'   => $this->resource->getImageType(),
        ];
    }
}
