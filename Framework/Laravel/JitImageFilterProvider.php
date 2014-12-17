<?php

/*
 * This File is part of the Thapp\JitImage\Framework\Laravel package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel;

use Thapp\JitImage\Resolver\FilterResolverInterface;

/**
 * @class JitImageFilterProvider
 *
 * @package Thapp\JitImage\Framework\Laravel
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class JitImageFilterProvider extends AppProvider
{
    /**
     * {@inheritdoc}
     */
    final public function boot()
    {
        $this->registerFilters($this->app['jmg.filters']);
    }

    /**
     * Registers a filter
     *
     * @param FilterResolverInterface $filters
     *
     * @return void
     */
    abstract protected function registerFilters(FilterResolverInterface $filters);
}
