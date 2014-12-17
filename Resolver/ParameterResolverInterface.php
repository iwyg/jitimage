<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage\Resolver package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

/**
 * @interface ParameterResolverInterface
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Resolver
 * @version $Id$
 */
interface ParameterResolverInterface
{
    public function resolveParameters(array $parameters);
}
