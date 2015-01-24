<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Resolver;

use Illuminate\Contracts\Foundation\Application;
use Thapp\JitImage\Framework\Common\Resolver\LazyResolverTrait;
use Thapp\JitImage\Framework\Common\Resolver\AbstractLazyCacheResolver;

/**
 * @class LazyCacheResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class LazyCacheResolver extends AbstractLazyCacheResolver
{
    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    /**
     * getCacheName
     *
     * @param string $alias
     *
     * @return string `NULL` if disabled
     */
    protected function getCacheName($alias)
    {
        $caches = $this->getCaches();

        if (!array_key_exists($alias, $caches)) {
            return $this->app['config']['jmg.default_cache'];
        }

        // cache disabled.
        if (false === $caches[$alias]) {
            return;
        }

        return $caches[$alias];
    }

    /**
     * Get the caches config values.
     *
     * @return array
     */
    protected function getCaches()
    {
        if (null === $this->settings) {
            $this->settings = $this->app['config']->get('jmg.caches', []);
        }

        return $this->settings;
    }

    /**
     * createHybridCache
     *
     * @return void
     */
    protected function createHybridCache()
    {
        throw new \LogicException('Not implemented yet.');
    }

    /**
     * getDefaultCachePath
     *
     * @return void
     */
    protected function getDefaultCachePath()
    {
        return $this->app['config']['jmg.default_cache_path'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCacheExpiryTime()
    {
        return $this->app['config']->get('jmg.cache_expiry_time', -1);
    }
}
