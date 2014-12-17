<?php

/*
 * This File is part of the Thapp\JitImage\Resolver package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

/**
 * @interface RecipeResolverInterface
 *
 * @package Thapp\JitImage\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface RecipeResolverInterface extends ResolverInterface
{
    /**
     * set
     *
     * @param array $recipes
     *
     * @return void
     */
    public function set(array $recipes);
}
