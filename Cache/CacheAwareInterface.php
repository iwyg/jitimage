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
 * @class CacheAwareInterface
 * @package Thapp\JitImage
 * @version $Id$
 */
interface CacheAwareInterface
{
    public function getCache();
}
