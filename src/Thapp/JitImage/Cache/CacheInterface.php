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
     * get
     *
     * @param mixed $id
     * @param mixed $raw
     * @access public
     * @return mixed
     */
    public function get($id, $raw = false);

    /**
     * has
     *
     * @param mixed $id
     * @access public
     * @return mixed
     */
    public function has($id);

    /**
     * put
     *
     * @param mixed $id
     * @param mixed $contents
     * @access public
     * @return mixed
     */
    public function put($id, $contents);

    /**
     * createKey
     *
     * @param mixed $src
     * @param mixed $fingerprint
     * @param string $prefix
     * @param string $suffix
     * @access public
     * @return mixed
     */
    public function createKey($src, $fingerprint = null, $prefix = 'io',  $suffix = 'f');

    /**
     * delete
     *
     * @param mixed $id
     * @access public
     * @return void
     */
    public function delete($id);

    /**
     * purge
     *
     * @access public
     * @return void
     */
    public function purge();

    /**
     * getRelPath
     *
     * @param  string $path
     *
     * @access public
     * @return string
     */
    public function getRelPath($path);

    /**
     * getIdFromUrl
     *
     * @param  string $url
     *
     * @access public
     * @return string
     */
    public function getIdFromUrl($url);
}

