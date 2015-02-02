<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

use Thapp\JitImage\Resolver\PathResolverInterface;
use Thapp\JitImage\Resolver\CacheResolverInterface;

/**
 * @class CacheClearer
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class CacheClearer
{
    private $cacheResolver;

    /**
     * Constructor.
     *
     * @param CacheResolverInterface $cacheResolver
     */
    public function __construct(CacheResolverInterface $cacheResolver)
    {
        $this->cacheResolver = $cacheResolver;
    }

    /**
     * Clears cache for a given path
     *
     * @param string $name
     *
     * @return boolean
     */
    public function clear($name = null)
    {
        if (null === $name) {
            return $this->clearAll();
        }

        if (!$this->cacheResolver->has($name) || !$cache = $this->cacheResolver->resolve($name)) {
            return false;
        }

        if ($cache->purge()) {
            return true;
        }

        return false;
    }

    public function clearImage($image, $prefix = null)
    {
        if (!$this->cacheResolver->has($prefix) || !$cache = $this->cacheResolver->resolve($prefix)) {
            return false;
        }

        return $cache->delete($image, $prefix);
    }

    private function clearAll()
    {
        $cleared = [];

        foreach ($this->cacheResolver as $name => $cache) {
            if (in_array($cache, $cleared)) {
                continue;
            }

            $this->clear($name);
            $cleared[] = $cache;
        }

        return true;
    }
}
