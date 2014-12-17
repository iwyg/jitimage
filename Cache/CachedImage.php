<?php

/**
 * This File is part of the Thapp\Image\Cache package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Image\Cache;

use \Thapp\Image\Image;

/**
 * @class CachedImage extends Image
 * @see Image
 *
 * @package Thapp\Image\Cache
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class CachedImage extends Image
{
    /**
     * cache
     *
     * @var mixed
     */
    private $cache;

    /**
     * source
     *
     * @var string
     */
    protected $source;

    public function setImageCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * create
     *
     * @param mixed $source
     * @param mixed $driver
     *
     * @access public
     * @return Image
     */
    public static function create($source = null, $driver = self::DRIVER_IMAGICK)
    {
        $image = static::getFactory($driver)->make(__CLASS__);

        if ($source) {
            $image->source($source);
        }

        return $image;
    }

    /**
     * source
     *
     * @param mixed $source
     *
     * @access public
     * @return mixed
     */
    public function source($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * process
     *
     * @access protected
     * @return mixed
     */
    protected function process()
    {
        $params = $this->compileExpression();

        $key = $this->getCacheKey($params, $this->filters);

        if ($this->cache->has($key)) {
            $source = $this->cache->getSource($key);
            $this->processor->load($source);
        } else {

            $this->processor->load($this->source);
            $params['filter'] = $this->filters;
            parent::process();

            $this->cache->set($key, $this->processor->getContents());
        }

        $this->filters = [];
        $this->source = null;
    }

    /**
     * getCacheKey
     *
     * @param array $params
     * @param array $filter
     *
     * @access protected
     * @return string
     */
    protected function getCacheKey(array $params, array $filter)
    {
        $fingerprint = $this->getImageFingerPrint($params, $filter);

        return $this->cache->createKey($this->source, $fingerprint, null, pathinfo($this->source, PATHINFO_EXTENSION));
    }

    /**
     * getImageFingerPrint
     *
     *
     * @access protected
     * @return mixed
     */
    protected function getImageFingerPrint(array $params, array $filters)
    {
        $p = implode(':', $params) . '::';

        $filterStr = '';

        foreach ($filters as $key => $args) {

            $filterStr .= $key;
            foreach ((array)$args as $arg => $val) {
                $filterStr .=  ':'. $arg . ':' .$val . ',';
            }
        }

        return rtrim($p.$filterStr, ',');
    }
}
