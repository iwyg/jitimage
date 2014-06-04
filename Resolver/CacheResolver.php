<?php

/**
 * This File is part of the Thapp\JitImage\Resolver package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

use \Thapp\Image\Cache\CacheInterface;
use \Thapp\JitImage\Resource\ImageResource;
use \Thapp\JitImage\Cache\CacheAwareInterface;

/**
 * @class CacheResolver
 * @package Thapp\JitImage
 * @version $Id$
 */
class CacheResolver implements ResolverInterface, CacheAwareInterface
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * resolve
     *
     * @param mixed $key
     *
     * @access public
     * @return void
     */
    public function resolve($key)
    {
        if ($this->cache->has($key)) {
            return $this->cache->get($key, false);
        }
    }

    /**
     * getCache
     *
     * @access public
     * @return void
     */
    public function getCache()
    {
        return $this->cache;
    }
}
