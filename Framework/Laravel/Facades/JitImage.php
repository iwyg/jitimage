<?php

/*
 * This File is part of the Thapp\JitImage\Framework\Laravel\Facades package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @class JitImage
 *
 * @package Thapp\JitImage\Framework\Laravel\Facades
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JitImage extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'jmg';
    }
}
