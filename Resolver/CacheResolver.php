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
    private $caches;
    private $cache;

    public function __construct(array $caches = [])
    {
        $this->set($caches);
    }

    /**
     * @return void
     */
    public function add($path, CacheInterface $cache)
    {
        $this->caches[$path] = $cache;
    }

    public function set(array $caches)
    {
        $this->caches = [];
        foreach ($caches as $path => $cache) {
            $this->add($path, $cache);
        }
    }

    /**
     * resolve
     *
     * @param mixed $key
     *
     * @access public
     * @return void
     */
    public function resolve($path)
    {
        if (array_key_exists($path, $this->caches)) {
            return $this->caches[$path];
        }
    }
}
