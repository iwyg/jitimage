<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Tests\View;

use Thapp\JitImage\View\Jmg;

/**
 * @class JmgTest
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JmgTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\JitImage\View\Jmg', new Jmg($this->mockResolver(), $this->mockRecipes()));
    }

    /** @test */
    public function takeShouldReturnGenerator()
    {
        $jmg = new Jmg($this->mockResolver(), $this->mockRecipes());
        $this->assertInstanceof('Thapp\JitImage\View\Generator', $jmg->take('image'));
    }

    protected function mockResolver()
    {
        return $this->getMock('Thapp\JitImage\Resolver\ImageResolverInterface');
    }

    protected function mockRecipes()
    {
        return $this->getMock('Thapp\JitImage\Resolver\RecipeResolverInterface');
    }
}
