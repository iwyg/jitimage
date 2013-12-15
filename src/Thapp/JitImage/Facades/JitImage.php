<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Facades;

use \Illuminate\Support\Facades\Facade;

/**
 * Class: JitImage
 *
 * @uses Facade
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImage extends Facade
{
    /**
     * @see Illuminate\Support\Facades\Facade#getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'jitimage';
    }
}
