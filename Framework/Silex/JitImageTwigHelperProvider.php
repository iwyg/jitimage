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
use Thapp\JitImage\View\Jmg;

/**
 * @class JitImageTwigServiceProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImageTwigHelperProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app->extend('twig', function ($twig, $app) {
            $twig->addExtension(
                new Jmg(
                    $app['jmg.resolver_image'],
                    $app['jmg.resolver_recipe'],
                    $app['jmg.default_path'],
                    $app['jmg.cache_path_prefix']
                )
            );

            return $twig;
        });
    }
}
