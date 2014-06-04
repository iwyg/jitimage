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
        list ($pattern, $params, $source, $filter) = $this->getPathRegexp();

        foreach (array_keys($routes) as $path) {
            $route = $router
                ->get($path . $pattern, 'Thapp\JitImage\Controller\LaravelController@getImage')
                ->where('params', $params)
                ->where('source', $source)
                ->where('filter', $filter);
        }

        $this->app->singleton('Thapp\JitImage\Controller\LaravelController', function () use ($routes) {
            $controller = new \Thapp\JitImage\Controller\LaravelController(
                new \Thapp\JitImage\Resolver\PathResolver($routes),
                new \Thapp\JitImage\Resolver\ImageResolver(
                    $this->app->make('Thapp\Image\Processor'),
                    $this->app['config']['jitimage::cache']['enabled'] ? new \Thapp\JitImage\Resolver\CacheResolver(
                        new \Thapp\Image\Cache\FilesystemCache(public_path(). '/cache')
                    ) : null,
                    new \Thapp\JitImage\Validator\ModeConstraints(
                        $this->app['config']['jitimage::mode_constraints'] ?: []
                    )
                )
            );

            $this->prepareController($controller);

            return $controller;
        });
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
