<?php

/**
 * This File is part of the tests package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Tests\JitImage;

use Mockery as m;
use \ReflectionClass;
use \ReflectionObject;

/**
 * Class: JitImageResolverTest
 *
 * @uses PHPUnit_Framework_TestCase
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * invokeMethod
     *
     * @param mixed $method
     * @param mixed $object
     * @param array $arguments
     * @access protected
     * @return mixed
     */
    protected function invokeMethod($method, $object, array $arguments = [])
    {
        $reflect  = new \ReflectionObject($object);
        $call     = $reflect->getMethod($method);

        $call->setAccessible(true);

        return $call->invokeArgs($object, $arguments);
    }

    /**
     * getPropertyValue
     *
     * @param mixed $property
     * @param mixed $object
     * @access protected
     * @return mixed
     */
    protected function getPropertyValue($property, $object)
    {

        $reflect  = new \ReflectionObject($object);
        $property = $reflect->getProperty($property);

        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * tearDown
     *
     * @access protected
     * @return mixed
     */
    protected function tearDown()
    {
        m::close();
    }
}
