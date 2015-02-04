<?php

/*
 * This File is part of the Thapp\JitImage\Tests package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests;

/**
 * @class TestHelperTrait
 *
 * @package Thapp\JitImage\Tests
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait TestHelperTrait
{
    protected function skipIfImagick()
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not loaded.');
        }
    }

    protected function skipIfGmagick()
    {
        if (!extension_loaded('gmagick')) {
            $this->markTestSkipped('Gmagick extension is not loaded.');
        }
    }
}
