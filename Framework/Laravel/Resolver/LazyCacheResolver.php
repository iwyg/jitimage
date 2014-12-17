<?php

/*
 * This File is part of the Thapp\JitImage\Framework\Laravel\Resolver package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Resolver;

use Thapp\JitImage\Resolver\CacheResolver;
use Illuminate\Contracts\Foundation\Application;

/**
 * @class LazyCacheResolver
 *
 * @package Thapp\JitImage\Framework\Laravel\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class LazyCacheResolver extends CacheResolver
{
    use LazyResolverTrait;

    private $app;
    private $settings;

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
     * {@inheritdoc}
     */
    public function resolve($alias)
    {
        if ($cache = parent::resolve($alias = trim($alias, '/'))) {
            return $cache;
        }

        if (null === $cache = $this->resolveCache($alias)) {
            return;
        }

        $this->add($alias, $cache);

        return $cache;
    }

    /**
     * resolveCache
     *
     * @param string $alias
     *
     * @return Thapp\JitImage\Cache\CacheInterface `NULL` if none is found.
     */
    protected function resolveCache($alias)
    {
        if (null === $name = $this->getCacheName($alias)) {
            return;
        }

        if (method_exists($this, $method = 'create'.ucfirst($name).'Cache')) {
            return call_user_func([$this, $method]);
        } elseif ($this->hasCustomCreator($name)) {
            return $this->callCustomCreator($name, [$this->app]);
        }
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
     * createFileCache
     *
     * @return void
     */
    protected function createFileCache()
    {
        $path =  $this->getDefaultCachePath();

        return new \Thapp\JitImage\Cache\FilesystemCache($path);
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
}
