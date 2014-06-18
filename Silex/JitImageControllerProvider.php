<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Silex;

use \Silex\Application;
use \Silex\ControllerCollection;
use \Silex\ControllerProviderInterface;
use \Thapp\JitImage\ProviderTrait as ProviderHelper;
use \Symfony\Component\HttpFoundation\Request;

/**
 * @class SilexControllerProvider
 * @package Thapp\JitImage
 * @version $Id$
 */
class JitImageControllerProvider implements ControllerProviderInterface
{
    use ProviderHelper;
    use ProviderTrait;

    private $paths;

    private $staticPaths;

    private $cacheConfig;

    public function __construct(array $paths = [], array $cacheConfig = [], array $static = [])
    {
        $this->paths = $paths;
        $this->cacheConfig = $cacheConfig;
        $this->staticPaths = $static;
    }

    /**
     * connect
     *
     * @param Application $app
     *
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $this->app = $app;

        return $this->registerControllers($app);
    }

    /**
     * registerControllers
     *
     * @param Application $app
     *
     * @access private
     * @return \Silex\ControllerCollection
     */
    private function registerControllers(Application $app)
    {

        $controllers = $app['controllers_factory'];

        $this->registerRoutes($app, $controllers);

        return $controllers;
    }

    private function registerRoutes(Application $app, ControllerCollection $controllers)
    {
        $this->registerCachedControllers($app, $controllers, $this->cacheConfig, $this->get('jitimage.cache.suffix', 'cached'));

        $this->registerStaticControllers($app, $controllers, $this->staticPaths);

        if ($disabled = $this->get('jitimage.disable_dynamic_processing', false)) {
            return;
        }

        list ($pattern, $params, $source, $filter) = $this->getPathRegexp();
        $requirements = compact('params', 'source', 'filter');

        foreach ($this->paths as $alias => $path) {
            $this->registerDynamicController(
                $app,
                $controllers,
                $alias,
                $pattern,
                $params,
                $source,
                $filter,
                $requirements
            );
        }
    }

    private function registerStaticControllers(Application $app, ControllerCollection $controllers, array $paths = [])
    {
        foreach ($paths as $routeAlias => $params) {
            list($route, $param) = $params;

            $controllers->get(
                $route . '/{' . $param . '}/{source}',
                ['uses' => 'Thapp\JitImage\Controller\LaravelController@getResource']
            )
            ->setRequirements([
                $param   => $routeAlias,
                'source' => '(.*)'
            ]);
        }
    }

    private function registerCachedControllers(
        Application $app,
        ControllerCollection $controllers,
        array $caches,
        $suffix
    ) {
        foreach (array_keys($caches) as $path) {
            $controllers->get(
                $pattern = rtrim($path, '/') . '/'. $suffix . '/{id}',
                'jitimage.controller:getCached'
            )
            ->setDefault('path', $path)
            ->setRequirements(['id' => '(.*\/){1}.*'])
            ->before(function (Request $request) use ($app) {
                $app['jitimage.controller']->setRequest($request);
            });
        }
    }

    private function registerDynamicController(
        Application $app,
        ControllerCollection $controllers,
        $path,
        $pattern,
        $params,
        $source,
        $filter,
        $requirements
    ) {
        $controllers->get($path . $pattern, 'jitimage.controller:getImage')
            ->addRequirements($requirements)
            ->setDefault('filter', null)
            ->setDefault('path', $path)
            ->before(function (Request $request) use ($app) {
                $app['jitimage.controller']->setRequest($request);
            });
    }
}
