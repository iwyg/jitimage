<?php

/*
 * This File is part of the Thapp\JitImage\Tests\Stubs package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Stubs;

use Illuminate\Contracts\Foundation\Application;

/**
 * @class LVA
 *
 * @package Thapp\JitImage\Tests\Stubs
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class LVA implements Application, \ArrayAccess
{
    public function version()
    {
    }
}
