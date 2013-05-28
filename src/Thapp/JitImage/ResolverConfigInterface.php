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
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface ResolverConfigInterface
{
    /**
     * Set a configuration attribute.
     *
     * @access public
     * @return void|boolean false on failure
     */
    public function set($attribute, $value);

    /**
     * Set the configuration array.
     *
     * @param array $attributes associative array containing key-value pairs of
     * configuration attributes.
     *
     * @access public
     * @return void
     */
    public function setAttributesArray(array $attributes);

    /**
     * Get a config value by its attribute name.
     *
     * @param mixed $attribute attribute name
     *
     * @access public
     * @return mixed
     */
    public function get($attribute = null);

    /**
     * Shortcut for get.
     *
     * @param string $attribute attribute name
     * @access public
     * @return mixed
     */
    public function __get($attribute);
}
