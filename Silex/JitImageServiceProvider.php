<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Silex;

use \Silex\Application;
use \Silex\ServiceProviderInterface;

/**
 * @class SilexServiceProvider
 * @package \Users\malcolm\www\image\src\Thapp\JitImage
 * @version $Id$
 */
class JitImageServiceProvider implements ServiceProviderInterface
{
    use ProviderTrait;

    public function register(Application $app)
    {
        $this->app = $app;

        $this->registerControllers($app);
    }

    public function boot(Application $app)
    {
    }

    protected function registerLoaders(Application $app)
    {
    }

    protected function registerControllers(Application $app)
    {


        $app['jitimage.image_resolver'] = function () {
            return new \Thapp\JitImage\Resolver\ImageResolver(
                new \Thapp\Image\Processor(
                    new \Thapp\Image\Driver\ImagickDriver
                )
            );
        };

        $app['jitimage.cache_resolver'] = function () {
            return new \Thapp\JitImage\Resolver\CacheResolver([

            ]);
        };

        $app['jitimage.path_resolver'] = function () use ($app) {
            return new \Thapp\JitImage\Resolver\PathResolver(
                $this->get('jitimage.paths', ['image' => null])
            );
        };


        $paths = $this->get('jitimage.paths', ['image' => null]);
        $app->mount('/', new JitImageControllerProvider(array_keys($paths)));
    }
}
