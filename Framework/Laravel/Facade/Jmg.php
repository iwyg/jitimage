<?php

/*
 * This File is part of the Thapp\JitImage\Framework\Laravel\Facade package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @class Jmg
 *
 * @package Thapp\JitImage\Framework\Laravel\Facade
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Jmg extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'jmg';
    }
}
