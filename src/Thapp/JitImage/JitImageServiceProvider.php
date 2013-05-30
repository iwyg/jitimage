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

use Illuminate\Support\ServiceProvider;

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
     * Register the image process driver.
     *
     * @access protected
     * @return void
     */
    protected function registerDriver()
    {
        $config = $this->app['config'];

        $driver = sprintf('\Thapp\JitImage\Driver\%sDriver', $driverName = ucfirst($config->get('jitimage::driver', 'gd')));
        $this->app->bind('Thapp\JitImage\Cache\CacheInterface', function ()
            {
                $path = storage_path() . '/jit';
                $cache = new \Thapp\JitImage\Cache\ImageCache(
                    $this->app['Thapp\JitImage\ImageInterface'],
                    $this->app['files'],
                    $path
                );
                return $cache;
            }
        );


        $this->app->bind('Thapp\JitImage\Driver\BinLocatorInterface', 'Thapp\JitImage\Driver\ImBinLocator');
        $this->app->bind('Thapp\JitImage\Driver\SourceLoaderInterface', 'Thapp\JitImage\Driver\ImageSourceLoader');

        $this->app->extend('Thapp\JitImage\Driver\BinLocatorInterface', function ($locator) use ($config)
            {
                extract($config->get('jitimage::imagemagick', ['path' => '/usr/local/bin', 'bin' => 'convert']));

                $locator->setConverterPath(sprintf('%s%s%s', rtrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, $bin));

                return $locator;
            }
        );

        $this->app->bind('Thapp\JitImage\ImageInterface', 'Thapp\JitImage\Image');
        $this->app->bind('Thapp\JitImage\Driver\DriverInterface', $driver);

        $this->app->extend('Thapp\JitImage\ImageInterface', function ($image)
            {
                $image->setQuality($this->app['config']->get('jitimage::quality', 80));
                return $image;
            }
        );


        $this->app['jitimage'] = $this->app->share(function () {
            return $this->app->make('Thapp\JitImage\JitImage');
        });

        $this->app['jitimage.cache'] = $this->app->share(function () {
            return $this->app->make('Thapp\JitImage\Cache\CacheInterface');
        });

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

        $this->app->bind('Thapp\JitImage\ResolverConfigInterface', function () use ($config) {

                $conf = [
                    'trusted_sites' => $this->extractDomains($config->get('jitimage::trusted-sites', [])),
                    'cache_prefix'  => $config->get('jitimage::cacheprefix', 'jit_'),
                    'cache_route'   => $config->get('jitimage::cacheroute', 'jit/storage'),
                    'base'          => $config->get('jitimage::base', public_path()),
                    'cache'         => in_array($config->getEnvironment(), $config->get('jitimage::cache', []))
                ];
                return new \Thapp\JitImage\JitResolveConfiguration($conf);
        });
    }

    /**
     * Register the response class on the ioc container.
     *
     * @access protected
     * @return void
     */
    protected function registerResponse()
    {
        $type = $this->app['config']->get('jitimage::response-type', 'generic');

        $this->app->bind('Thapp\JitImage\Response\FileResponseInterface',
            sprintf('Thapp\JitImage\Response\%sFileResponse', ucfirst($type))
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

        $recepies   = $config->get('jitimage::recepies', []);
        $route      = $config->get('jitimage::route', 'image');
        $cacheroute = $config->get('jitimage::cacheroute', 'jit/storage');

        $this->registerCacheRoute($cacheroute);

        if (false === $this->registerStaticRoutes($recepies, $route)) {
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
     * @param  array $recepies array of prefined processing instructions
     * @param  string $route baseroute name
     *
     * @access protected
     * @return void|boolean false
     */
    protected function registerStaticRoutes(array $recepies = [], $route)
    {
        if (empty($recepies)) {
            return false;
        }

        foreach ($recepies as $aliasRoute => $formular) {
            $this->app['router']
                ->get($route . '/' . $aliasRoute . '/{source}', [
                  'uses' => 'Thapp\JitImage\Controller\JitController@getResource'
                  ])
                ->where('source', '(([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6}).*?(?=\/filter:.*)?)')
                ->defaults('parameter', $formular);
        }
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
            ->where('params', '([5|6](\/\d+){1}|[0]|[1|4](\/\d+){2}|[2](\/\d+){3}|[3](\/\d+){3}\/?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})?)')
            // match the image source:
            ->where('source', '(([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6}).*?(?=\/filter:.*)?)')
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
        $this->app['command.jitimage.clearcache'] = $this->app->share(function($app)
		{
			return new Console\JitImageCacheClearCommand($app['jitimage.cache']);
        });

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
        $this->app->extend('Thapp\JitImage\Driver\DriverInterface', function ($driver) use ($driverName, $filters) {

            $addFilters = $this->app['events']->fire('jitimage.registerfitler', [$driverName]);

            foreach ($addFilters as $filter) {
                foreach ($filter as $name => $class) {
                    if (class_exists($class)) {
                        $driver->registerFilter($name, $class);
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
        });
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
     * getFilters
     *
     * @access private
     * @return array
     */
    private function getFilters()
    {
        return $this->app['config']->get('jitimage::filter', []);
    }

    /**
     * extractDomains
     *
     * @access private
     * @return mixed
     */
    private function extractDomains($sites)
    {
        $trustedSites = [];

        foreach ($sites as $site) {
            extract(parse_url($site));
            $trustedSites[] = $host;
        }

        return $trustedSites;
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
}
