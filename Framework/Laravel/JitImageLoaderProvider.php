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

use Thapp\JitImage\Resolver\LoaderResolverInterface;

/**
 * @class JitImageFilterProvider
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class JitImageLoaderProvider extends AppProvider
{
    /**
     * {@inheritdoc}
     */
    final public function boot()
    {
        $this->registerLoaders($this->app['jmg.loaders']);
    }

    /**
     * {@inheritdoc}
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
    abstract protected function registerLoaders(LoaderResolverInterface $loaders);
}
