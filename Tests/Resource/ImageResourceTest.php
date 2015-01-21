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

use Thapp\JitImage\Resource\ImageResource;

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
        $this->assertInstanceof('Thapp\JitImage\Resource\ResourceInterface', new ImageResource);
    }

    /** @test */
    public function itShouldGetDiminsions()
    {
        $res = new ImageResource(null, 200, 100);

        $this->assertEquals(200, $res->getWidth());
        $this->assertEquals(100, $res->getHeight());
    }

    /** @test */
    public function itShouldGetDiminsionsIfOnlyPathIsGiven()
    {
        $res = new ImageResource(__DIR__.'/../Fixures/pattern.png');

        $this->assertEquals(600, $res->getWidth());
        $this->assertEquals(600, $res->getHeight());
    }

    /** @test */
    public function itShouldGetDiminsionsIfOnlyContentIsGiven()
    {
        $file = __DIR__.'/../Fixures/pattern.png';
        $res = new ImageResource;
        $res->setContents(file_get_contents($file));

        $this->assertEquals(600, $res->getHeight());
        $this->assertEquals(600, $res->getWidth());
    }
}
