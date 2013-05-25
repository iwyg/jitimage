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
 * Class: ImBinLocator
 *
 * @implements BinLocatorInterface
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImBinLocator implements BinLocatorInterface
{
    /**
     * path
     *
     * @var string
     */
    protected $path;

    /**
     * setConverterPath
     *
     * @param mixed $path
     * @access public
     * @return mixed
     */
    public function setConverterPath($path)
    {
        return $this->path = $path;
    }

    /**
     * getConverterPath
     *
     * @param mixed $param
     * @access public
     * @return mixed
     */
    public function getConverterPath()
    {
        return $this->path;
    }
}
