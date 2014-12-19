<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Common\Resolver;

/**
 * @trait LazyResolverTrait
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait LazyResolverTrait
{
    private $customCreators = [];

    /**
     * extend
     *
     * @param mixed $name
     * @param callable $creator
     *
     * @return void
     */
    public function extend($name, callable $creator)
    {
        $this->customCreators[$name] = $creator;
    }

    /**
     * getCustomCreator
     *
     * @param mixed $name
     *
     * @return void
     */
    protected function getCustomCreator($name)
    {
        return $this->customCreators[$name];
    }

    /**
     * callCustomCreator
     *
     * @param array $arguments
     *
     * @return void
     */
    protected function callCustomCreator($name, array $arguments = [])
    {
        return call_user_func_array($this->getCustomCreator($name), $arguments);
    }

    /**
     * hasCustomCreator
     *
     * @param mixed $name
     *
     * @return void
     */
    protected function hasCustomCreator($name)
    {
        return isset($this->customCreators[$name]);
    }
}
