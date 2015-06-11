<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

use Thapp\JitImage\Loader\LoaderInterface;

/**
 * @class LoaderResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class LoaderResolver implements LoaderResolverInterface
{
    protected $loaders = [];

    public function resolve($prefix)
    {
        if (isset($this->loaders[$prefix])) {
            return $this->loaders[$prefix];
        }
    }

    public function add($prefix, LoaderInterface $loader)
    {
        $this->loaders[$prefix] = $loader;
    }
}
