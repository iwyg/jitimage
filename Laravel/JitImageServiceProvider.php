<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Laravel;

use \Illuminate\Routing\Router;
use \Illuminate\Support\ServiceProvider;
use \Symfony\Component\Filesystem\Filesystem;
use \Thapp\JitImage\ProviderTrait;

/**
 * @class JitImageServiceProvider extends ServiceProvider
 * @see ServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class JitImageServiceProvider extends ServiceProvider
{
    use ProviderTrait;

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Thapp\JitImage\Adapter\FlysystemCache', function ($app) {
            @mkdir($path = storage_path().'/fly', 0755, true);

            return new \Thapp\JitImage\Adapter\FlysystemCache(
                $app['GrahamCampbell\Flysystem\Managers\FlysystemManager'],
                'public/cache',
                $path
            );
        });

        $this->registerLoader();
        $this->registerWriter();
        $this->registerDriver();
        $this->registerProcessor();

    }

    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $this->package('thapp/jitimage', 'jitimage', __DIR__);

        $this->registerRoutingAndCaches(
            $this->app['router'],
            $this->app['config']->get('jitimage::recipes', []),
            $this->getCacheConfig()
        );

        $this->registerJitImage();
        $this->registerCommands();
    }

    /**
     * Registers Controllers and Caches
     *
     * @param Router $router
     * @param array  $recipes
     * @param array  $cacheConfig
     *
     * @return void
     */
    protected function registerRoutingAndCaches(Router $router, array $recipes = [], array $cacheConfig = [])
    {
        $paths   = $this->app['config']->get('jitimage::paths');

        $caches  = $this->registerCaches($router, $paths, $cacheConfig);
        $recipes = $this->registerStaticRoutes($router, $recipes, $paths);

        $this->app['jitimage.recipe_resolver'] = $this->app->share(function () use ($recipes) {
            return new \Thapp\JitImage\Resolver\RecipeResolver($recipes);
        });

        $this->registerResolvers($caches, $paths);
        $this->registerControllerService($router, $recipes, $paths);


        if ($this->app['config']->get('jitimage::disable_dynamic_processing', false)) {
            return;
        }

        // laravel doesn't handle default params, so replace the pattern;
        list ($params, $source, $filter) = array_slice($this->getPathRegexp(), 1);
        $pattern = '/{params}/{source}/{filter?}';

        foreach ($paths as $alias => $path) {
            $this->registerDynamicController($router, $alias, $pattern, $params, $source, $filter);
        }
    }

    /**
     * Registers cachecontrollers
     *
     * @param Router $router
     * @param array  $paths
     * @param array  $cacheConfig
     *
     * @return void
     */
    protected function registerCaches(Router $router, array $paths, array $cacheConfig)
    {
        $caches = [];

        list ($useCache, $default, $suffix, $cachePath, $cacheRoutes) = $cacheConfig;

        foreach ($paths as $alias => $path) {

            if (isset($cacheRoutes[$alias]['enabled']) && true !== $cacheRoutes[$alias]['enabled']) {
                continue;
            }

            if (!isset($cacheRoutes[$alias])) {
                $caches[$alias] = [true, $cachePath];
            }

            if (isset($cacheRoutes[$alias]['path'])) {
                $caches[$alias] = [true, $cacheRoutes[$alias]['path']];
            } elseif (isset($cacheRoutes[$alias]['service'])) {
                $caches[$alias] = $cacheRoutes[$alias]['service'];
            } else {
                $caches[$alias] = [true, $cachePath];
            }

            // setup cached routes
            $this->registerCachedController($router, $alias, $suffix);
        }

        return $caches;
    }


    protected function registerCommands()
    {
        $this->app['commands.jitimage.clearcache'] = $this->app->share(function ($app) {
            return new \Thapp\JitImage\Laravel\Console\ClearCacheCommand(
                $app['jitimage.cache_resolver']
            );
        });

        $this->commands('commands.jitimage.clearcache');
    }

    /**
     * registerJitImage
     *
     * @return void
     */
    protected function registerJitImage()
    {
        $this->app['jitimage'] = $this->app->share(function ($app) {
            return new \Thapp\JitImage\JitImage(
                $app['jitimage.image_resolver'],
                $app['jitimage.path_resolver'],
                $app['jitimage.recipe_resolver'],
                $app['config']->get('jitimage::cache.suffix', 'cached'),
                $app['config']->get('jitimage::cache.default_path', null)
            );
        });
    }

    /**
     * registerWriter
     *
     * @access protected
     * @return void
     */
    protected function registerWriter()
    {
        $this->app['jitimage.image_writer'] = $this->app->share(function () {
            return new \Thapp\Image\Writer\FilesystemWriter;
        });
    }

    /**
     * registerProcessor
     *
     * @return void
     */
    protected function registerProcessor()
    {
        $this->app['jitimage.image_processor'] = $this->app->share(function () {
            $quality = $this->app['config']->get('jitimage::quality', 80);
            $processor = new \Thapp\JitImage\JitImageProcessor(
                $this->app['jitimage.image_driver'],
                $this->app['jitimage.image_writer']
            );
            $processor->setQuality($quality);

            return $processor;
        });
    }

    /**
     * registerLoader
     *
     * @access protected
     * @return void
     */
    protected function registerLoader()
    {
        // register the filesystem loader:
        $this->app->singleton('Thapp\Image\Loader\FileSystemLoader');

        // register the curl loader:
        $this->app->singleton('Thapp\Image\Loader\RemoteLoader', function ($app) {
            return new \Thapp\Image\Loader\RemoteLoader(
                $app['config']->get('jitimage::trusted_sites', [])
            );
        });

        // register the delegating loader:
        $this->app['jitimage.source_loader'] = $this->app->share(function () {

            $loaders = [];

            foreach ($this->app['config']['jitimage::loaders'] as $loaderClass) {
                $loaders[] = $this->app->make($loaderClass);
            }

            return new \Thapp\Image\Loader\DelegatingLoader($loaders);
        });
    }

    /**
     * registerDriver
     *
     * @return void
     */
    protected function registerDriver()
    {
        $this->app['jitimage.image_driver'] = $this->app->share(function ($app) {

            $driver = $this->app['config']->get('jitimage::driver', 'gd');

            if (method_exists($this, $method = sprintf('register%sDriver', ucfirst($driver)))) {
                return call_user_func([$this, $method]);
            }

            throw new \InvalidArgumentException(sprintf('invalid driver %s', $driver));
        });
    }

    /**
     * getCacheConfig
     *
     * @return array
     */
    private function getCacheConfig()
    {
        $defaultPath = storage_path() . DIRECTORY_SEPARATOR . 'jitimage';

        return [
            $this->app['config']->get('jitimage::cache.enabled', true),
            $this->app['config']->get('jitimage::cache.default', 'image'),
            $this->app['config']->get('jitimage::cache.suffix', 'cached'),
            $this->app['config']->get('jitimage::cache.path', $defaultPath),
            $this->app['config']->get('jitimage::cache.paths', [])
        ];
    }

    /**
     * registeImagickDriver
     *
     * @return void
     */
    private function registerImagickDriver()
    {
        $driver = new \Thapp\Image\Driver\ImagickDriver(
            $this->app['jitimage.source_loader']
        );

        return $driver;
    }

    /**
     * registeImaDriver
     *
     * @return void
     */
    private function registerImDriver()
    {
        $driver = new \Thapp\Image\Driver\ImDriver(
            $this->app['jitimage.source_loader'],
            new \Thapp\Image\Driver\ImBinLocator($app['config']['jitimage::imagick'] ?: null)
        );

        return $driver;
    }

    /**
     * registeImaDriver
     *
     * @return void
     */
    private function registerGdDriver()
    {
        $driver = new \Thapp\Image\Driver\GdDriver(
            $this->app['jitimage.source_loader']
        );

        return $driver;
    }

    /**
     * registerControllerService
     *
     * @param array $recipes
     * @param array $routes
     *
     * @return void
     */
    private function registerControllerService(Router $router, array $recipes, array $routes)
    {
        // register the ImageController
        $this->app->singleton(
            'Thapp\JitImage\Controller\LaravelController',
            function ($app) use ($router, $recipes, $routes) {

                $controller = new \Thapp\JitImage\Controller\LaravelController(
                    $app['jitimage.path_resolver'],
                    $app['jitimage.image_resolver']
                );

                $controller->setRouter($router);
                $controller->setRequest($app['request']);

                if (!empty($recipes)) {
                    $controller->setRecieps($app['jitimage.recipe_resolver']);
                }

                return $controller;
            }
        );
    }

    /**
     * registerDynamicController
     *
     * @param Router $router
     * @param mixed $path
     * @param string $pattern
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return void
     */
    private function registerDynamicController(Router $router, $path, $pattern, $params, $source, $filter)
    {
        $router->get($path . $pattern, 'Thapp\JitImage\Controller\LaravelController@getImage')
            ->where('params', $params)
            ->where('source', $source)
            ->where('filter', $filter);
    }

    /**
     * registerCachedController
     *
     * @param Router $router
     * @param string $path
     * @param string $suffix
     *
     * @return void
     */
    private function registerCachedController(Router $router, $path, $suffix)
    {
        $r = $router->get(
            rtrim($path, '/') . '/{suffix}/{id}',
            'Thapp\JitImage\Controller\LaravelController@getCachedResource'
        )
        ->where('id', '(.*\/){1}.*')
        ->where('suffix', $suffix)
        ->defaults('path', $path);
    }

    /**
     * @param Router $router
     * @param array $recipes
     * @param array $routes
     *
     * @return array
     */
    private function registerStaticRoutes(Router $router, array $recipes, array $routes = [])
    {
        $resolved = [];

        foreach ($recipes as $route => $params) {

            if (!in_array($route, array_keys($routes))) {
                continue;
            }

            foreach ($params as $routeAlias => $formular) {
                $param = str_replace('/', '_', $routeAlias);
                $resolved[$routeAlias] = $formular;

                $this->registerStaticController($router, $route, $param, $routeAlias);
            }
        }

        return $resolved;
    }

    /**
     * registerStaticController
     *
     * @param Router $router
     * @param string $route
     * @param string $param
     * @param string $routeAlias
     *
     * @return void
     */
    private function registerStaticController(Router $router, $route, $param, $routeAlias)
    {
        $router->get(
            $route . '/{' . $param . '}/{source}',
            ['uses' => 'Thapp\JitImage\Controller\LaravelController@getResource']
        )
        ->where($param, $routeAlias)
        ->where('source', '(.*)');
    }

    /**
     * registerResolvers
     *
     * @param array $caches
     * @param array $routes
     *
     * @return void
     */
    private function registerResolvers(array $caches, array $routes)
    {
        // register the CacheResolver
        $this->app['jitimage.cache_resolver'] = $this->app->share(function () use ($caches) {
            return new \Thapp\JitImage\Resolver\CacheResolver($this->initCaches($caches));
        });

        // register the PathResolver
        $this->app['jitimage.path_resolver'] = $this->app->share(function () use ($routes) {
            return new \Thapp\JitImage\Resolver\PathResolver($routes);
        });

        // register the ImageResolver
        $this->app['jitimage.image_resolver'] = $this->app->share(function ($app) {

            return new \Thapp\JitImage\Resolver\ImageResolver(
                $app['jitimage.image_processor'],
                $app['jitimage.cache_resolver'],
                new \Thapp\JitImage\Validator\ModeConstraints(
                    $this->app['config']->get('jitimage::mode_constraints', [])
                )
            );
        });
    }

    /**
     * setUpCaches
     *
     * @param array $caches
     *
     * @return void
     */
    private function initCaches(array $caches = [])
    {
        $cache = [];

        foreach ($caches as $route => $c) {
            if (is_array($c) && false !== $c[0]) {
                $cache[$route] = $this->getDefaultCache($c[1]);
                continue;
            }

            $cache[$route] = $this->getOptCache($c);
        }

        return $cache;
    }

    private function getOptCache($service)
    {
        return $this->app[$service];
    }
}
