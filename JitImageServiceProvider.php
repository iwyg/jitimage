<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use \Illuminate\Routing\Router;
use \Illuminate\Support\ServiceProvider;

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
    public function register()
    {
        $this->package('thapp/jitimage', 'jitimage', __DIR__);

        $this->registerControllers($this->app['router'], $this->app['config']['jitimage::routes']);

        $this->registerLoader();
        $this->registerWriter();
        $this->registerProcessor();
        $this->registerDriver();
    }

    public function boot()
    {
    }

    /**
     * registerWriter
     *
     * @access protected
     * @return void
     */
    protected function registerWriter()
    {
        $this->app['image.writer'] = $this->app->share(function () {
            return new \Thapp\Image\Writer\FilesystemWriter;
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
        $this->app->singleton('Thapp\Image\Loader\DelegatingLoader', function () {

            $loaders = [];

            foreach ($this->app['config']['jitimage::loaders'] as $loaderClass) {
                $loaders[] = $this->app->make($loaderClass);
            }

            return new \Thapp\Image\Loader\DelegatingLoader($loaders);
        });
    }

    /**
     * registerProcessor
     *
     * @return void
     */
    protected function registerProcessor()
    {
        $this->app->singleton('Thapp\Image\Processor', function () {
            return new \Thapp\Image\Processor($this->app['image.driver'], $this->app['image.writer']);
        });
    }

    /**
     * registerDriver
     *
     * @return void
     */
    protected function registerDriver()
    {
        $driver = $this->app['config']['jitimage::driver'];

        if (method_exists($this, $method = sprintf('register%sDriver', ucfirst($driver)))) {
            return call_user_func([$this, $method]);
        }

        throw new \InvalidArgumentException(sprintf('invalid driver %s', $driver));
    }

    /**
     * registeImagickDriver
     *
     * @return void
     */
    protected function registerImagickDriver()
    {
        $this->app['image.driver'] = $this->app->share(function () {
            return new \Thapp\Image\Driver\ImagickDriver(
                $this->app->make('Thapp\Image\Loader\DelegatingLoader')
            );
        });
    }

    /**
     * registeImaDriver
     *
     * @return void
     */
    protected function registerImDriver()
    {
        $this->app['image.driver'] = $this->app->share(function () {
            return $this->app->make('Thapp\Image\Driver\ImDriver');
        });
    }

    /**
     * registeImaDriver
     *
     * @return void
     */
    protected function registerGdDriver()
    {
        $this->app['image.driver'] = $this->app->share(function () {
            $driver = $this->app->make('Thapp\Image\Driver\GdDriver');
            $driver->setQuality(80);

            return $driver;
        });
    }

    /**
     * registerLoaders
     *
     * @return void
     */
    protected function registerLoaders()
    {
        foreach ($this->app['config']['jitimage::loaders'] as $loaderClass) {

        }
    }

    /**
     * registerControllers
     *
     * @return void
     */
    protected function registerControllers(Router $router, array $routes)
    {
        if (!$disabled = $this->app['config']->get('jitimage::disable_dynamic_processing', false)) {
            list ($pattern, $params, $source, $filter) = $this->getPathRegexp();
        }

        $useCache    = $this->app['config']->get('jitimage::cache.enabled', true);
        $default     = $this->app['config']->get('jitimage::cache.default', 'image');
        $suffix      = $this->app['config']->get('jitimage::cache.suffix', 'cached');
        $cachepath   = $this->app['config']->get('jitimage::cache.path', storage_path() . DIRECTORY_SEPARATOR . 'jitimage');
        $cacheRoutes = $this->app['config']->get('jitimage::cache.routes', []);

        $caches = [];

        foreach (array_keys($routes) as $path) {

            !$disabled && $router
                ->get($path . $pattern, 'Thapp\JitImage\Controller\LaravelController@getImage')
                ->where('params', $params)
                ->where('source', $source)
                ->where('filter', $filter);

            if (!$useCache) {
                continue;
            }

            $enabled = true;

            $usePath = false;

            if (isset($cacheRoutes[$path]['enabled']) && !($enabled = $cacheRoutes[$path]['enabled'])) {
                continue;
            }

            if ($usePath = isset($cacheRoutes[$path]) && isset($cacheRoutes[$path]['service'])) {
                $caches[$path] = $cacheRoutes[$path]['service'];

            } else {

                if (isset($cacheRoutes[$path]['path'])) {
                    $cachepath = $cacheRoutes[$path]['path'];
                }

                $caches[$path] = [true, $cachepath];
            }


            $this->setUpCachedRoute($router, $path, $suffix);
        }

        $recipes = $this->registerStaticRoutes($router, $this->app['config']->get('jitimage::recipes', []), $routes);

        $this->app->singleton('Thapp\JitImage\Controller\LaravelController', function () use ($routes, $caches, $recipes) {

            $cache = [];

            foreach ($caches as $route => $c) {
                if (is_array($cache) && false !== $c[0]) {
                    $cache[$route] = $this->getDefaultCache($c[1]);
                } else {
                    $cache[$route] = $this->getOptCache($c);
                }
            }

            $controller = new \Thapp\JitImage\Controller\LaravelController(
                new \Thapp\JitImage\Resolver\PathResolver($routes),
                new \Thapp\JitImage\Resolver\ImageResolver(
                    $this->app->make('Thapp\Image\Processor'),
                    new \Thapp\JitImage\Resolver\CacheResolver($cache),
                    new \Thapp\JitImage\Validator\ModeConstraints(
                        $this->app['config']['jitimage::mode_constraints'] ?: []
                    )
                )
            );

            $this->prepareController($controller);

            if (!empty($recipes)) {
                $controller->setRecieps(new \Thapp\JitImage\Resolver\RecipeResolver($recipes));
            }

            return $controller;
        });

    }

    private function registerStaticRoutes($router, array $recipes, array $routes = [])
    {
        $resolved = [];

        foreach ($recipes as $route => $params) {
            if (!in_array($route, array_keys($routes))) {
                continue;
            }
            foreach ($params as $routeAlias => $formular) {

                $param = str_replace('/', '_', $routeAlias);

                $resolved[$routeAlias] = $formular;

                $router
                    ->get(
                        $route . '/{' . $param . '}/{source}',
                        ['uses' => 'Thapp\JitImage\Controller\LaravelController@getResource']
                    )
                    ->where($param, $routeAlias)
                    ->where('source', '(.*)');
            }
        }

        return $resolved;
    }

    public function setUpCaches()
    {

    }

    /**
     * getDefaultCache
     *
     * @param string $path
     *
     * @return \Thapp\Image\Cache\CacheInterface
     */
    private function setUpCachedRoute($router, $path, $suffix)
    {
        $router
            ->get(rtrim($path, '/') . '/'. $suffix . '/{id}', 'Thapp\JitImage\Controller\LaravelController@getCached')
            ->where('id', '(.*\/){1}.*');
    }

    /**
     * getDefaultCache
     *
     * @param string $path
     *
     * @return \Thapp\Image\Cache\CacheInterface
     */
    private function getDefaultCache($path)
    {
        return new \Thapp\Image\Cache\FilesystemCache($path);
    }

    /**
     * @param mixed $service
     *
     * @return mixed
     */
    private function getOptCache($service)
    {
        return $this->app->make($service);
    }

    /**
     * prepareController
     *
     * @param \Illuminate\Routing\Controller $controller
     *
     * @access protected
     * @return void
     */
    protected function prepareController(\Illuminate\Routing\Controller $controller)
    {
        $controller->setRouter($this->app['router']);
        $controller->setRequest($this->app['request']);
    }

    /**
     * getPathRegexp
     *
     * @return array
     */
    private function getPathRegexp()
    {
        return [
            '/{params}/{source?}/{filter?}',
            '([5|6](\/\d+){1}|[0]|[1|4](\/\d+){2}|[2](\/\d+){3}|[3](\/\d+){3}\/?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})?)',
            '((([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6})?).*?.(?=(\/filter:.*)?))',
            '(filter:.*)'
        ];
    }
}
