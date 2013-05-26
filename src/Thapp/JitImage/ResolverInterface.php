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
 * Class: ResolverInterface
 *
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface ResolverInterface
{
    /**
     * resolve
     *
     * @access public
     * @return mixed
     */
    public function resolve();

    /**
     * resolveFromCache
     *
     * @param mixed $id
     * @access public
     * @return mixed
     */
    public function resolveFromCache($id);

    /**
     * getCached
     *
     * @access public
     * @return mixed
     */
    public function getCached();

    /**
     * setFilter
     *
     * @param mixed $filter
     * @access public
     * @return mixed
     */
    public function setFilter($filter = null);

    /**
     * setParameter
     *
     * @param mixed $parameter
     * @access public
     * @return mixed
     */
    public function setParameter($parameter);

    /**
     * setSource
     *
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function setSource($source);

    /**
     * disableCache
     *
     * @access public
     * @return mixed
     */
    public function disableCache();
}
