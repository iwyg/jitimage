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

/**
 * @class FilterResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface FilterResolverInterface extends ResolverInterface
{
    /**
     * add
     *
     * @param mixed $filter
     * @param string $name
     * @param string $alias
     *
     * @return void
     */
    public function add($filters, $name, $alias = null);
}
