<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Controller;

/**
 * @interface ImageControllerInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface ImageControllerInterface
{
    public function getImage($alias, $params = null, $source = null, $filter = null);

    public function getResource($route, $alias, $source);

    public function getCached($path, $id);
}
