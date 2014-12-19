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
use Pimple\ServiceProviderInterface;

/**
 * @class JitImageServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImageServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $this->prepareConfig($app);
        $this->registerResolver($app);
        $this->registerProcessor($app);
        $this->registerController($app);
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
        $app['jmg.processor'] = function () use ($app) {
            $imagine = $this->getImagineClass($app['jmg.driver']);
            return new \Thapp\JitImage\Imagine\Processor(new $imagine);
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

        $app->mount('/', new JitImageControllerProvider);
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

    /**
     * getImagine
     *
     * @param mixed $driver
     *
     * @return void
     */
    protected function getImagineClass($driver)
    {
        switch ($driver) {
            case 'gd':
                return '\Imagine\Gd\Imagine';
            case 'imagick':
                return '\Imagine\Imagick\Imagine';
            case 'gmagick':
                return '\Imagine\Gmagick\Imagine';
            default:
                break;
        }

        throw new \InvalidArgumentException('Invalid driver "'. $driver .'".');
    }
}
