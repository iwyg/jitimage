<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Cache package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

/**
 * Class: CacheInterface
 *
 *
 * @package
 * @version
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
}

