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
 * Class: ResolveConfiguration
 *
 * @implements Thapp\JitImage\ResolverConfigInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitResolveConfiguration implements ResolverConfigInterface
{
    /**
     * attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * allowedAttributes
     *
     * @var mixed
     */
    protected static $allowedAttributes = [
        'cache', 'base', 'trusted_sites', 'cache_prefix', 'cache_route', 'base_route', 'format_filter'
    ];

    /**
     * Create a new JitResolveConfiguration object.
     *
     * @param array $data initial configuration data.
     * @access public
     */
    public function __construct(array $data = [])
    {
        return $this->setAttributesArray($data);
    }

    /**
     * Will set a configuration value, given its attribute name is contained in
     * `static::$allowedAttributes`
     *
     * {@inheritdoc}
     */
    public function set($attribute, $value)
    {
        if (!in_array($attribute, static::$allowedAttributes)) {
            return false;
        }
        $this->attributes[$attribute] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributesArray(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->set($attribute, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($attribute = null)
    {
        if (is_null($attribute)) {
            return $this->attributes;
        }

        if (!in_array($attribute, static::$allowedAttributes)) {
            return;
        }
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($attribute)
    {
        return $this->get($attribute);
    }
}
