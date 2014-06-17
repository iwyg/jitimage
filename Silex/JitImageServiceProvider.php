<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Silex;

use \Silex\Application;
use \Thapp\JitImage\ProviderTrait as CommonProviderTrait;
use \Silex\ServiceProviderInterface;

/**
 * @class SilexServiceProvider
 * @package \Users\malcolm\www\image\src\Thapp\JitImage
 * @version $Id$
 */
class JitImageServiceProvider implements ServiceProviderInterface
{
    use ProviderTrait;
    use CommonProviderTrait;

    public function register(Application $app)
    {
        $this->app = $app;

        $this->registerLoader($app);
        $this->registerWriter($app);
        $this->registerDriver($app);
        $this->registerProcessor($app);
    }

    public function boot(Application $app)
    {
        $this->registerRoutingAndCaches(
            $app,
            $this->get('jitimage.recipes', []),
            $this->getCacheConfig($app)
        );

        $this->registerJitImage($app);
    }

    /**
     * registerRoutingAndCaches
     *
     * @param Application $app
     * @param array $recipes
     * @param array $cacheConfig
     *
     * @access protected
     * @return void
     */
    protected function registerRoutingAndCaches(Application $app, array $recipes, array $cacheConfig)
    {

        $this->registerResolvers(
            $app,
            $caches = $this->registerCaches(
                $paths = $this->get('jitimage.paths', ['image' => getcwd()]),
                $cacheConfig
            ),
            $paths
        );


        $resolved = $this->resolveRecipes($recipes, $paths);


        $app['jitimage.recipe_resolver'] = $app->share(function () use ($resolved) {
            return new \Thapp\JitImage\Resolver\RecipeResolver($resolved['recipes']);
        });

        $app->mount('/', new JitImageControllerProvider($paths, $caches, $resolved['params']));

        $this->registerControllerService($app, $resolved['recipes'], $paths);
    }

    private function registerControllerService(Application $app, array $recipes, array $paths)
    {
        $app['jitimage.controller'] = $app->share(function () use ($app) {
            $controller =  new \Thapp\JitImage\Controller\SilexController(
                $app['jitimage.path_resolver'],
                $app['jitimage.image_resolver']
            );

            if (!empty($recipes)) {
                $controller->setRecieps($app['jitimage.recipe_resolver']);
            }

            return $controller;
        });
    }

    private function resolveRecipes(array $recipes, array $routes = [])
    {
        $resolved = ['recipes' => [], 'params' => []];

        foreach ($recipes as $route => $params) {

            if (!in_array($route, array_keys($routes))) {
                continue;
            }

            foreach ($params as $routeAlias => $formular) {
                $param = str_replace('/', '_', $routeAlias);
                $resolved['recipes'][$routeAlias] = $formular;
                $resolved['params'][$routeAlias] = [$route, $param];
            }
        }

        return $resolved;
    }

    protected function registerCaches(array $paths, array $cacheConfig)
    {
        $caches = [];

        list ($useCache, $default, $cachePath, $cacheRoutes) = $cacheConfig;

        if (!$useCache) {
            return $caches;
        }

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
        }

        return $caches;
    }

    /**
     * registerJitImage
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerJitImage(Application $app)
    {
        $app['jitimage'] = $app->share(function ($app) {
            return new \Thapp\JitImage\JitImage(
                $app['jitimage.image_resolver'],
                $app['jitimage.path_resolver'],
                $app['jitimage.recipe_resolver'],
                $this->get('jitimage.cache.suffix', 'cached'),
                $this->get('jitimage.default_path', null)
            );
        });
    }

    /**
     * registerLoader
     *
     * @access protected
     * @return void
     */
    protected function registerLoader(Application $app)
    {
        // register the filesystem loader:
        $app['jitimage.filesystem_loader'] = $app->share(function () {
            return new \Thapp\Image\Loader\FileSystemLoader;
        });

        // register the curl loader:
        $app['jitimage.remote_loader'] = $app->share(function ($app) {
            return new \Thapp\Image\Loader\RemoteLoader(
                $this->get('jitimage.trusted_sites', [])
            );
        });

        // register the delegating loader:
        $app['jitimage.source_loader'] = $app->share(function ($app) {

            $loaders = [];

            foreach ($this->get('jitimage.loaders', []) as $loaderService) {
                $loaders[] = $app[$loaderService];
            }

            return new \Thapp\Image\Loader\DelegatingLoader($loaders);
        });
    }

    protected function registerWriter(Application $app)
    {
        $app['jitimage.image_writer'] = $app->share(function () {
            return new \Thapp\Image\Writer\FilesystemWriter;
        });
    }

    /**
     * registerDriver
     *
     * @return void
     */
    protected function registerDriver(Application $app)
    {
        $app['jitimage.image_driver'] = $app->share(function ($app) {

            $driver = $this->get('jitimage.driver', 'gd');

            if (method_exists($this, $method = sprintf('register%sDriver', ucfirst($driver)))) {
                return call_user_func([$this, $method], $app);
            }

            throw new \InvalidArgumentException(sprintf('invalid driver %s', $driver));
        });
    }

    /**
     * registerProcessor
     *
     * @return void
     */
    protected function registerProcessor(Application $app)
    {
        $app['jitimage.image_processor'] = $app->share(function ($app) {
            $quality = $this->get('jitimage.quality', 80);

            try {
                $processor = new \Thapp\JitImage\JitImageProcessor(
                    $app['jitimage.image_driver'],
                    $app['jitimage.image_writer']
                );
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                die;
            }

            $processor->setQuality($quality);

            return $processor;
        });
    }

    protected function registerControllers(Application $app)
    {


        $app['jitimage.image_resolver'] = function () {
            return new \Thapp\JitImage\Resolver\ImageResolver(
                new \Thapp\Image\Processor(
                    new \Thapp\Image\Driver\ImagickDriver
                )
            );
        };

        $app['jitimage.cache_resolver'] = function () {
            return new \Thapp\JitImage\Resolver\CacheResolver([

            ]);
        };

        $app['jitimage.path_resolver'] = function () use ($app) {
            return new \Thapp\JitImage\Resolver\PathResolver(
                $this->get('jitimage.paths', ['image' => null])
            );
        };


        $paths = $this->get('jitimage.paths', ['image' => null]);
        $app->mount('/', new JitImageControllerProvider(array_keys($paths)));
    }

    /**
     * registeImagickDriver
     *
     * @return void
     */
    private function registerImagickDriver(Application $app)
    {
        $driver = new \Thapp\Image\Driver\ImagickDriver(
            $app['jitimage.source_loader']
        );

        return $driver;
    }

    /**
     * registeImaDriver
     *
     * @return void
     */
    private function registerImDriver(Application $app)
    {
        $driver = new \Thapp\Image\Driver\ImDriver(
            $app['jitimage.source_loader'],
            new \Thapp\Image\Driver\ImBinLocator($this->get('jitimage::imagick', null))
        );

        return $driver;
    }

    /**
     * registeImaDriver
     *
     * @return void
     */
    private function registerGdDriver(Application $app)
    {
        $driver = new \Thapp\Image\Driver\GdDriver(
            $app['jitimage.source_loader']
        );

        return $driver;
    }

    private function registerResolvers(Application $app, array $caches, array $routes)
    {
        // register the CacheResolver
        $app['jitimage.cache_resolver'] = $app->share(function () use ($caches) {
            return new \Thapp\JitImage\Resolver\CacheResolver($this->initCaches($caches));
        });

        // register the PathResolver
        $app['jitimage.path_resolver'] = $app->share(function () use ($routes) {
            return new \Thapp\JitImage\Resolver\PathResolver($routes);
        });

        // register the ImageResolver
        $app['jitimage.image_resolver'] = $app->share(function ($app) {

            return new \Thapp\JitImage\Resolver\ImageResolver(
                $app['jitimage.image_processor'],
                $app['jitimage.cache_resolver'],
                new \Thapp\JitImage\Validator\ModeConstraints(
                    $this->get('jitimage.mode_constraints', [])
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
            if (is_array($cache) && false !== $c[0]) {
                $cache[$route] = $this->getDefaultCache($c[1]);
                continue;
            }

            $cache[$route] = $this->getOptCache($c);
        }

        return $cache;
    }

    /**
     * getCacheConfig
     *
     * @return array
     */
    private function getCacheConfig(Application $app)
    {
        $defaultPath = getcwd() . DIRECTORY_SEPARATOR . 'jitimage';

        return [
            $this->get('jitimage.cache.enabled', true),
            $this->get('jitimage.cache.default', 'image'),
            $this->get('jitimage.cache.path', $defaultPath),
            $this->get('jitimage.cache.paths', [])
        ];
    }
}
