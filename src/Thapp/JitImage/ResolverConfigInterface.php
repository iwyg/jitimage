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

/**
 * Interface: ResolverConfigInterface
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface ResolverConfigInterface
{
    /**
     * set
     *
     * @access public
     * @return void|boolean false on failure
     */
    public function set($attribute, $value);

    /**
     * setAttributesArray
     *
     * @param array $attributes
     * @access public
     * @return void
     */
    public function setAttributesArray(array $attributes);

    /**
     * get
     *
     * @access public
     * @return mixed
     */
    public function get($attribute = null);

    /**
     * __get
     *
     * @param mixed $attribute
     * @access public
     * @return mixed
     */
    public function __get($attribute);
}
