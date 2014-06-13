<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\Resource;

use \Thapp\JitImage\Resource\ImageResource;

/**
 * @class ImageResolverTest
 * @package Thapp\JitImage
 * @version $Id$
 */
class ImageResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $resource = new ImageResource;

        $this->assertInstanceof('\Thapp\Image\Resource\ResourceInterface', $resource);
    }
}
