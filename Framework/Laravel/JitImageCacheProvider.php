<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel;

use Thapp\JitImage\Resolver\CacheResolverInterface;

/**
 * @class JitImageFilterProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class JitImageCacheProvider extends AppProvider
{
    /**
     * {@inheritdoc}
     */
    final public function boot()
    {
        $this->registerCaches($this->app['jmg.caches']);
    }

    /**
     * Registers a filter
     *
     * @param FilterResolverInterface $filters
     *
     * @return void
     */
    abstract protected function registerCaches(CacheResolverInterface $caches);
}
