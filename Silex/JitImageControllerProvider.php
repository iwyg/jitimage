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

    public function __construct(array $paths = [])
    {
        $this->paths = $paths;
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
        $app['jitimage.controller'] = $app->share(function () use ($app) {
            return new \Thapp\JitImage\Controller\SilexController(
                $app['jitimage.path_resolver'],
                $app['jitimage.image_resolver']
            );
        });

        $controllers = $app['controllers_factory'];

        $this->registerROutes($app, $controllers);

        return $controllers;
    }

    private function registerRoutes(Application $app, $controllers)
    {
        if (!$disabled = $this->get('jitimage.disable_dynamic_processing', false)) {
            list ($pattern, $params, $source, $filter) = $this->getPathRegexp();
            $requirements = compact('params', 'source', 'filter');
        }

        $useCache    = $this->get('jitimage.cache.enabled', true);
        $default     = $this->get('jitimage.cache.default', 'image');
        $suffix      = $this->get('jitimage.cache.suffix', 'cached');
        $cachepath   = $this->get(
            'jitimage.cache.path',
            storage_path() . DIRECTORY_SEPARATOR . 'jitimage'
        );

        $cacheRoutes = $app->get('jitimage.cache.routes', []);

        $caches = [];

        foreach ($this->paths as $alias => $path) {

            if (!$disabled) {
                $controllers->get(rtrim($path, '/') . $pattern, 'jitimage.controller:getImage')
                    ->addRequirements($requirements)
                    ->setDefault('filter', null)
                    ->setDefault('path', $alias)

                    ->before(function (Request $request) use ($app) {
                        $app['jitimage.controller']->setRequest($request);
                    });
            }
        }
    }
}
