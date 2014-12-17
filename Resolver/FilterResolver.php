<?php

/*
 * This File is part of the Thapp\JitImage\Resolver package
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
 * @package Thapp\JitImage\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FilterResolver implements FilterResolverInterface
{
    private $aliases = [];
    private $filters = [];

    /**
     * {@inheritdoc}
     */
    public function resolve($name)
    {
        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];

            return $this->filters[$name];
        }
    }

    /**
     * add
     *
     * @param mixed $filter
     * @param string $name
     * @param string $alias
     *
     * @return void
     */
    public function add($filters, $name, $alias = null)
    {
        $alias = $alias ?: $name;
        $this->aliases[$alias] = $name;

        $this->filters[$name] = is_array($filters) ? $filters : [$filters];
    }
}
