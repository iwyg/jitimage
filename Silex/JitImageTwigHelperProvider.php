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
use \Silex\ServiceProviderInterface;
use \Thapp\JitImage\Twig\JitImageExtension;

/**
 * @class JitImageTwigHelperProvider
 * @package Thapp\JitImage
 * @version $Id$
 */
class JitImageTwigHelperProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
            $twig->addExtension(new JitImageExtension($app['jitimage']));

            return $twig;
        }));
    }

    public function boot(Application $app)
    {
    }
}
