<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Silex;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Thapp\JitImage\Framework\Common\ProviderHelperTrait;

/**
 * @class JitImageServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImageControllerProvider implements ControllerProviderInterface
{
    use ProviderHelperTrait;

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        return $this->registerControllers($app);
    }

    /**
     * registerControllers
     *
     * @param Application $app
     *
     * @return ControllerCollection
     */
    protected function registerControllers(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $this->registerRecipeController($app, $controllers);

        if (!$app['jmg.disable_dynamic_processing']) {
            $this->registerDynamicController($app, $controllers);
        }

        $this->registerCachedController($app, $controllers);

        return $controllers;
    }

    /**
     * registerDynamicController
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerDynamicController(Application $app, ControllerCollection $controllers)
    {
        list ($pattern, $params, $source, $filter) = $this->getPathRegexp();

        foreach ($app['jmg.paths'] as $path => $filePath) {
            $path = rtrim($path, '/');
            $route = $controllers->get($ptrn = $path. $pattern, [$app['jmg.controller'], 'getImageAction'])
                ->setDefault('path', $path)
                ->setDefault('filter', '')
                ->setRequirements([
                    'params' => $params,
                    'source' => $source,
                    'filter' => $filter,
                ]);

            $route->before(function (Request $request) use ($app, $route) {
                $route->setDefault('request', $request);
                $app['jmg.controller']->setImageResolver($app['jmg.resolver_image']);
                $app['jmg.controller']->setRecieps($app['jmg.resolver_recipe']);
            });
        }
    }

    /**
     * registerRecipeController
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerRecipeController(Application $app, ControllerCollection $controllers)
    {
        $paths = $app['jmg.paths'];

        foreach ($app['jmg.recipes'] as $recipe => $data) {
            if (2 !== count($data)) {
                continue;
            }

            $route = $controllers->get($recipe . '/{source}', [$app['jmg.controller'], 'getResourceAction'])
                ->setDefault('recipe', $recipe)
                ->setRequirements([
                    'source' => '(.*)'
                    ]);

            $route->before(function (Request $request) use ($app, $route) {
                $route->setDefault('request', $request);
                $app['jmg.controller']->setImageResolver($app['jmg.resolver_image']);
                $app['jmg.controller']->setRecipeResolver($app['jmg.resolver_recipe']);
            });
        }
    }

    /**
     * registerCachedController
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerCachedController(Application $app, ControllerCollection $controllers)
    {
        $caches = $app['jmg.caches'];
        $suffix = $app['jmg.cache_path_prefix'];

        foreach ($app['jmg.paths'] as $alias => $path) {
            if (isset($caches[$alias]) && false === $caches[$alias]) {
                continue;
            }

            $route = $controllers->get('/'.trim($suffix).'/{'.trim($alias, '/').'}/{id}', [$app['jmg.controller'], 'getCachedAction'])
                ->setDefault('path', $alias)
                ->setRequirements(['id' => '(.*\/){1}.*']);

            $route->before(function (Request $request) use ($app, $route) {
                $route->setDefault('request', $request);
                $app['jmg.controller']->setImageResolver($app['jmg.resolver_image']);
            });
        }
    }
}
