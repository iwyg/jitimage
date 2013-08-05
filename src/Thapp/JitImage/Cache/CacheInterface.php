<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

/**
 * Interface: CacheInterface
 *
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface CacheInterface
{
    /**
     * Get an image from the cache.
     *
     * @param string $key  cached id
     * @param bool   $raw whather to return the contents or an image object
     * @access public
     * @return mixed|string|void
     */
    public function get($key, $raw = false);

    /**
     * Determine weather a cached file exists or not.
     *
     * @param string $key the cache identifier key
     *
     * @access public
     * @return boolean
     */
    public function has($key);

    /**
     * Put a image to the cache.
     *
     * @param string $key        the cache key
     * @param string $contents  the image contents
     *
     * @access public
     * @return void
     */
    public function put($key, $contents);

    /**
     * Deletes a specifix item from the cache dircetory.
     *
     * @param string $key the cache key
     *
     * @access public
     * @return void
     */
    public function delete($key);

    /**
     * Empty the cache directory.
     *
     * @access public
     * @return void
     */
    public function purge();

    /**
     * Get the relative cache path from a given url of a cached file.
     *
     * @param string $path file url of a cached image
     *
     * @access public
     * @return string
     */
    public function getRelPath($path);

    /**
     * Retreive the cache id key from a cached file url.
     *
     * @param string $url
     *
     * @access public
     * @return string
     */
    public function getIdFromUrl($url);

    /**
     * Create a cache id key from a file url.
     *
     * @param string $src         the source url, e.g `uploads/images/image.jpg`
     * @param string $fingerprint a unique string that identifies this item
     * @param string $prefix      string to prefix the cachefile name
     * @param string $suffix      file extension for the cachefile
     *
     * @access public
     * @return string
     */
    public function createKey($src, $fingerprint = null, $prefix = 'io', $suffix = 'f');
}
