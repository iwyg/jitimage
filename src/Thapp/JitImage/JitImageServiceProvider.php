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

use \Thapp\JitImage\Proxy\ProxyImage;
use \Thapp\JitImage\Cache\CachedImage;
use \Illuminate\Support\ServiceProvider;
use \Symfony\Component\Filesystem\Filesystem;

/**
 * Class: JitImageServiceProvider
 *
 * @uses ServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author  Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageServiceProvider extends ServiceProvider
{
    protected $deferred = true;

    /**
     * register
     *
     * @access public
     * @return void
     */
    public function register()
    {
        $this->package('thapp/jitimage');

        $this->registerDriver();
        $this->registerResolver();
    }

    /**
     * boot
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        $this->registerResponse();
        $this->registerController();
        $this->regsiterCommands();
    }

    /**
     * provides
     *
     * @access public
     * @return array
     */
    public function provides()
    {
        return ['jitimage', 'jitimage.cache'];
    }

    /**
     * Register the image process driver.
     *
     * @access protected
     * @return void
     */
    protected function registerDriver()
    {
        $app = $this->app;

        $config  = $app['config'];

        $storage = $config->get('jitimage::cache.path');

        $driver = sprintf(
            '\Thapp\JitImage\Driver\%sDriver',
            $driverName = ucfirst($config->get('jitimage::driver', 'gd'))
        );

        $app->bind(
            'Thapp\JitImage\Cache\CacheInterface',
            function () use ($storage) {
                $cache = new \Thapp\JitImage\Cache\ImageCache(
                    new CachedImage,
                    //$this->app['Thapp\JitImage\ImageInterface'],
                    new Filesystem,
                    $storage . '/jit'
                );
                return $cache;
            }
        );


        $app->bind('Thapp\JitImage\Driver\SourceLoaderInterface', 'Thapp\JitImage\Driver\ImageSourceLoader');

        $app->bind('Thapp\JitImage\Driver\BinLocatorInterface', function () use ($config) {
            $locator = new \Thapp\JitImage\Driver\ImBinLocator;
            extract($config->get('jitimage::imagemagick', ['path' => '/usr/local/bin', 'bin' => 'convert']));

            $locator->setConverterPath(
                sprintf('%s%s%s', rtrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $bin)
            );

            return $locator;
        });


        $this->app->bind('Thapp\JitImage\Driver\DriverInterface', function () use ($driver) {
            return $this->app->make($driver);
        });
        
        $this->app->bind('Thapp\JitImage\ImageInterface', function () use ($app) {
            return new ProxyImage(function () use ($app) {
                $image = new Image($app->make('Thapp\JitImage\Driver\DriverInterface'));
                $image->setQuality($app['config']->get('jitimage::quality', 80));
                return $image;
            });
        });

        //$this->app->extend(
        //    'Thapp\JitImage\ImageInterface',
        //    function ($image) {
        //        $image->setQuality($this->app['config']->get('jitimage::quality', 80));
        //        return $image;
        //    }
        //);

        $this->app['jitimage'] = $this->app->share(
            function () use ($app) {
                $resolver = $app->make('Thapp\JitImage\ResolverInterface');
                $image = new JitImage($resolver, \URL::to('/'));
                return $image;
            }
        );

        $this->app['jitimage.cache'] = $this->app->share(
            function () {
                return $this->app->make('Thapp\JitImage\Cache\CacheInterface');
            }
        );

        $this->registerFilter($driverName, $this->getFilters());
    }

    /**
     * Register the ResolverInterface on its implementation.
     *
     * @access protected
     * @return void
     */
    protected function registerResolver()
    {
        $config = $this->app['config'];

        $this->app->singleton('Thapp\JitImage\ResolverInterface', 'Thapp\JitImage\JitImageResolver');

        $this->app->bind(
            'Thapp\JitImage\ResolverConfigInterface',
            function () use ($config) {

                $conf = [
                    'trusted_sites' => $config->get('jitimage::trusted-sites', []),
                    'cache_prefix'  => $config->get('jitimage::cache.prefix', 'jit_'),
                    'base_route'    => $config->get('jitimage::route', 'images'),
                    'cache_route'   => $config->get('jitimage::cache.route', 'jit/storage'),
                    'base'          => $config->get('jitimage::base', public_path()),
                    'cache'         => in_array(
                        $config->getEnvironment(),
                        $config->get('jitimage::cache.environments', [])
                    ),
                    'format_filter'  => $config->get('jitimage::filter.Convert', 'conv')
                ];
                return new \Thapp\JitImage\JitResolveConfiguration($conf);
            }
        );
    }

    /**
     * Register the response class on the ioc container.
     *
     * @access protected
     * @return void
     */
    protected function registerResponse()
    {
        $app = $this->app;
        $type = $this->app['config']->get('jitimage::response-type', 'generic');

        $response = sprintf('Thapp\JitImage\Response\%sFileResponse', ucfirst($type));
        $this->app->bind(
            'Thapp\JitImage\Response\FileResponseInterface',
            function () use ($response, $app) {
                return new $response($app['request']);
            }
        );
    }

    /**
     * Register the image controller
     *
     * @access protected
     * @return void;
     */
    protected function registerController()
    {
        $config = $this->app['config'];

        $recipes    = $config->get('jitimage::recipes', []);
        $route      = $config->get('jitimage::route', 'image');
        $cacheroute = $config->get('jitimage::cache.route', 'jit/storage');

        $this->registerCacheRoute($cacheroute);

        if (false === $this->registerStaticRoutes($recipes, $route)) {
            $this->registerDynanmicRoute($route);
        }
    }

    /**
     * Register the controller method for retreiving cached images
     *
     * @param string $route
     * @access protected
     * @return void
     */
    protected function registerCacheRoute($route)
    {
        $this->app['router']
            ->get($route . '/{id}', 'Thapp\JitImage\Controller\JitController@getCached')
            ->where('id', '(.*\/){1}.*');
    }

    /**
     * Register static routes.
     *
     * @param  array $recipes array of prefined processing instructions
     * @param  string $route baseroute name
     *
     * @access protected
     * @return void|boolean false
     */
    protected function registerStaticRoutes(array $recipes = [], $route = null)
    {
        if (empty($recipes)) {
            return false;
        }

        $ctrl = 'Thapp\JitImage\Controller\JitController';

        foreach ($recipes as $aliasRoute => $formular) {

            $param = str_replace('/', '_', $aliasRoute);

            $this->app['router']
                ->get(
                    $route . '/{' . $param . '}/{source}',
                    ['uses' => $ctrl . '@getResource']
                )
                ->where($param, $aliasRoute)
                ->where('source', '(.*)');
        }

        //$this->app->bind($ctrl);
        //$this->app->extend($ctrl, function ($controller) use ($recipes) {
        //    $controller->setRecieps(new \Thapp\JitImage\RecipeResolver($recipes));
        //    return $controller;
        //});
        
        $this->app->bind($ctrl, function () use ($ctrl, $recipes) {
            $controller = new $ctrl(
                $this->app->make('Thapp\JitImage\ResolverInterface'),
                $this->app->make('Thapp\JitImage\Response\FileResponseInterface')
            );
            $controller->setRecieps(new \Thapp\JitImage\RecipeResolver($recipes));
            return $controller;
        });
    }

    /**
     * Register dynanmic routes.
     *
     * @param  string $route baseroute name
     * @access protected
     * @return void
     */
    protected function registerDynanmicRoute($route)
    {
        $this->app['router']
            ->get($route . '/{params}/{source}/{filter?}', 'Thapp\JitImage\Controller\JitController@getImage')
            // matching different modes:
            ->where(
                'params',
                '([5|6](\/\d+){1}|[0]|[1|4](\/\d+){2}|[2](\/\d+){3}|[3](\/\d+){3}\/?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})?)'
            )
            // match the image source:
            ->where('source', '((([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6})?).*?.(?=(\/filter:.*)?))')
            // match the filter:
            ->where('filter', '(filter:.*)');
    }

    /**
     * regsiterCommands
     *
     * @access protected
     * @return void
     */
    protected function regsiterCommands()
    {
        $this->app['command.jitimage.clearcache'] = $this->app->share(
            function ($app) {
                return new Console\JitImageCacheClearCommand($app['jitimage.cache']);
            }
        );

        $this->commands('command.jitimage.clearcache');
    }

    /**
     * registerFilter
     *
     * @param mixed $driverName
     * @access protected
     * @return void
     */
    protected function registerFilter($driverName, $filters)
    {
        $this->app->extend(
            'Thapp\JitImage\Driver\DriverInterface',
            function ($driver) use ($driverName, $filters) {

                $addFilters = $this->app['events']->fire('jitimage.registerfilter', [$driverName]);

                foreach ($addFilters as $filter) {
                    foreach ((array)$filter as $name => $class) {
                        if (class_exists($class)) {
                            $driver->registerFilter($name, $class);
                        } else {
                            throw new \InvalidArgumentException(
                                sprintf('Filterclass %s for %s driver does not exists', $class, $driverName)
                            );
                        }
                    }
                }

                foreach ($filters as $name => $filter) {
                    $driver->registerFilter(
                        $filter,
                        sprintf('Thapp\JitImage\Filter\%s\%s%sFilter', $name, $driverName, ucfirst($filter))
                    );
                }

                return $driver;
            }
        );
    }

    /**
     * getFilters
     *
     * @access private
     * @return array
     */
    private function getFilters()
    {
        return $this->app['config']->get('jitimage::filter', []);
    }
}
