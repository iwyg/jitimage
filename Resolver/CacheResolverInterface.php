<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

use Thapp\JitImage\Cache\CacheInterface;
use Thapp\JitImage\Resource\ImageResource;

/**
 * @class CacheResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface CacheResolverInterface extends ResolverInterface
{

    /**
     * Add a cache instance to the resolver
     *
     * @param sting $alias
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function add($alias, CacheInterface $cache);

    /**
     * Add an array of cache instances to the resolver
     *
     * @param sting $alias
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function set(array $caches);

    /**
     * A cache was registered with this name.
     *
     * @param string $alias
     *
     * @return boolean
     */
    public function has($alias);
}
