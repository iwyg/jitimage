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

use Pimple\Container;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Thapp\JitImage\Framework\Common\ProviderHelperTrait;

/**
 * @class JitImageServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImageServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    use ProviderHelperTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $this->prepareConfig($app);
        $this->registerResolver($app);
        $this->registerProcessor($app);
        $this->registerController($app);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->mount('/', new JitImageControllerProvider);
    }

    /**
     * registerProcessor
     *
     * @param Container $app
     *
     * @return void
     */
    protected function registerProcessor(Container $app)
    {
        $useImagine = $app['jmg.use_imagine'];
        $class  = $useImagine ? 'Thapp\JitImage\Imagine\Processor' : 'Thapp\JitImage\Image\Processor';

        $app['jmg.processor'] = function () use ($app, $class, $useImagine) {
            $source = $useImagine ? $this->getImagineClass($app['jmg.driver']) : $this->getSourceClass($app['jmg.driver']);
            return new $class(
                new $source,
                $app['jmg.resolver_filter']
            );
        };
    }

    /**
     * registerResolver
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerResolver(Container $app)
    {
        $app['jmg.resolver_image'] = function () use ($app) {
            return new \Thapp\JitImage\Resolver\ImageResolver(
                $app['jmg.processor'],
                $app['jmg.resolver_path'],
                $app['jmg.resolver_loader'],
                $app['jmg.resolver_cache'],
                $app['jmg.mode_validator']
            );
        };

        $app['jmg.resolver_path'] = function () use ($app) {
            return new \Thapp\JitImage\Resolver\PathResolver($app['jmg.paths']);
        };

        $app['jmg.resolver_loader'] = function () use ($app) {
            return new \Thapp\JitImage\Framework\Silex\Resolver\LazyLoaderResolver($app);
        };

        $app['jmg.resolver_cache'] = function () use ($app) {
            return new \Thapp\JitImage\Framework\Silex\Resolver\LazyCacheResolver($app);
        };

        $app['jmg.resolver_recipe'] = function () use ($app) {
            return new \Thapp\JitImage\Resolver\RecipeResolver($app['jmg.recipes']);
        };

        $app['jmg.resolver_filter'] = function () use ($app) {
            return new \Thapp\JitImage\Resolver\FilterResolver;
        };

        $app['jmg.mode_validator'] = function () use ($app) {
            return new \Thapp\JitImage\Validator\ModeConstraints($app['jmg.mode_constraints']);
        };
    }

    /**
     * registerController
     *
     * @param Application $app
     *
     * @return void
     */
    protected function registerController(Container $app)
    {
        $app['jmg.controller'] = function () use ($app) {
            return new \Thapp\JitImage\Http\Controller;
        };
    }

    /**
     * prepareConfig
     *
     * @param Application $application
     *
     * @return void
     */
    protected function prepareConfig(Container $application)
    {
        $app = $application;
        require __DIR__.'/resource/config.php';
    }
}
