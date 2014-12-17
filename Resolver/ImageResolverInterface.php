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
 * @interface ImageResolverInterface
 *
 * @package Thapp\JitImage\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface ImageResolverInterface extends ParameterResolverInterface
{
    /**
     * resolverCache
     *
     * @param array $parameters
     *
     * @return void
     */
    public function resolveCached(array $parameters);

    /**
     * Get the image processor.
     *
     * @return ImageProcessorInterface
     */
    public function getProcessor();

    /**
     * Get the path resolver.
     *
     * @return ResolverInterface
     */
    public function getPathResolver();

    /**
     * Get the cache resolver
     *
     * @return CacheResolverInterface
     */
    public function getCacheResolver();

    /**
     * Get the Loader resolver.
     *
     * @return LoaderResolverInterface
     */
    public function getLoaderResolver();
}
