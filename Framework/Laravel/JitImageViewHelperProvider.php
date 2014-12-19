<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel;

use Thapp\JitImage\View\Jmg;
use Thapp\JitImage\Resolver\LoaderResolverInterface;

/**
 * @class JitImageFilterProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImageViewHelperProvider extends AppProvider
{
    /**
     * {@inheritdoc}
     */
    final public function boot()
    {
        $this->registerViewHelper($this->app['jmg']);
    }

    /**
     * {@inheritdoc}
     * @TODO: only register when view is actually booted.
     */
    public function when()
    {
        return ['jmg.processor.boot'];
    }

    /**
     * Register custom loaders
     *
     * @param LoaderResolverInterface $loaders
     *
     * @return void
     */
    protected function registerViewHelper(Jmg $jmg)
    {
        $this->app['view']->share('jmg', $jmg);
    }
}
