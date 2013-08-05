<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

/**
 * Interface: ResolverInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface ResolverInterface
{
    /**
     * resolve and image that sould be processed
     *
     * @access public
     * @return mixed|bool false on failure, ans instance of \Thapp\JitImage\ImageInterface
     * on success
     */
    public function resolve();

    /**
     * resolve an image with its cache-id name
     *
     * @param mixed $id
     * @access public
     * @return mixed|bool false on failure, ans instance of \Thapp\JitImage\ImageInterface
     * on success
     */
    public function resolveFromCache($id);

    /**
     * always returns the cached instance of \Thapp\JitImage\ImageInterface
     *
     * @access public
     * @return mixed|bool false on failure, ans instance of \Thapp\JitImage\ImageInterface
     * on success
     */
    public function getCached();

    /**
     * set the root path from where to resolve an image file
     *
     * @param string $base
     *
     * @access public
     * @return void
     */
    public function setResolveBase($base = '/');

    /**
     * set the process parameter necessary for
     * processing the image
     *
     * @param string $parameter the parameter url string
     * @access public
     * @return void
     */
    public function setParameter($parameter);

    /**
     * set the image source url
     *
     * @param string $source
     * @access public
     * @return void
     */
    public function setSource($source);

    /**
     * set optional filter parameter
     *
     * @param string $filter
     * @access public
     * @return void
     */
    public function setFilter($filter = null);

    /**
     * disable iamges from beeing cached cached
     *
     * @access public
     * @return void
     */
    public function disableCache();

    /**
     * get the resolvable url of a cached image
     *
     * @param \Thapp\JitImage\ImageInterface $cachedImage an image to retreived
     * from \Thapp\JitImage\ResolverInterface#getGached()
     *
     * @access public
     * @return mixed
     */
    public function getCachedUrl(ImageInterface $cachedImage);

    /**
     * Get the current request url of the image.
     *
     * @param \Thapp\JitImage\ImageInterface $image an image retreived
     * @access public
     * @return string
     */
    public function getImageUrl(ImageInterface $image);

    /**
     * Get a parameter by its name.
     *
     * @param  string $key
     * @access public
     * @return mixed|array the value of the patameter, all parametes if $key is not
     * set or null if no parameter was found.
     */
    public function getParameter($key = null);

    /**
     * close and cleanup parameter of the resolver
     *
     * @access public
     * @return void
     */
    public function close();
}
