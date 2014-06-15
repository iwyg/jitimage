<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests;

use \Mockery as m;
use \Thapp\JitImage\JitImage;

/**
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class JitImageTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $ires = m::mock('\Thapp\JitImage\Resolver\ImageResolver');
        $pres = m::mock('\Thapp\JitImage\Resolver\PathResolver');

        $this->assertInstanceof('\Thapp\JitImage\JitImage', new JitImage($ires, $pres));
    }
}
