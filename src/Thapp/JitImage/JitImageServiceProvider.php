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
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
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

    }

    /**
     * registerDriver
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

        $this->app->bind('Thapp\JitImage\ResolverInterface', 'Thapp\JitImage\JitImageResolver');
        $this->app->bind(
            'Thapp\JitImage\ResolverConfigInterface', function () use ($config) {

                $conf = [
                    'recepies'      => $config->get('jitimage::recepies', []),
                    'trusted-sites' => $config->get('jitimage::trusted-sites', []),
                    'base'          => $config->get('jitimage::base-sites', public_path()),
                    'cache'         => in_array($config->getEnvironment(), $config->get('jitimage::cache', []))
                ];
                return new \Thapp\JitImage\JitResolveConfiguration($conf);
        });

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

        $this->registerFilter($driverName, $this->getFilters());
    }
    /**
     * registerController
     *
     * @access protected
     * @return void;
     */
    protected function registerController()
    {
        $recepies = $this->app['config']->get('jitimage::recepies', []);
        $route    = $this->app['config']->get('jitimage::route', 'image');

        if (!empty($recepies)) {
            return $this->registerRecepies($recepies, $route);
        }


        $this->app['router']
            ->get($route . '/{params}/{source}/{filter?}', 'Thapp\JitImage\Controller\JitController@getImage')
            ->where('params', '(\d+\/?){1,4}([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})?')
            ->where('source', '(([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6}).*?(?=\/filter:.*)?)')
            ->where('filter', '(filter:.*)');
    }

    /**
     * registerRecepies
     *
     * @param array $recepies
     * @access protected
     * @return void
     */
    protected function registerRecepies(array $recepies, $route)
    {
        foreach ($recepies as $aliasRoute => $formular) {
            $this->app['router']
                ->get($route . '/' . $aliasRoute . '/{source}', [
                  'uses' => 'Thapp\JitImage\Controller\JitController@getResource'
                  ])
                ->where('source', '(([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6}).*?(?=\/filter:.*)?)')
                ->defaults('parameter', $formular);
        }
    }

    protected function getParamsRegexp()
    {

    }


    /**
     * registerFilter
     *
     * @param mixed $driverName
     * @access protected
     * @return mixed
     */
    protected function registerFilter($driverName, $filters)
    {
        $this->app->extend('Thapp\JitImage\Driver\DriverInterface', function ($driver) use ($driverName, $filters){
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
     * getFilters
     *
     * @access protected
     * @return array
     */
    protected function getFilters()
    {
        return $this->app['config']->get('jitimage::filter', []);
    }

    /**
     * boot
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        $this->registerController();
    }
}
