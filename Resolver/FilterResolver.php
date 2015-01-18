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

use Thapp\JitImage\Filter\FilterInterface;

/**
 * @class FilterResolver
 *
 * @package Thapp\JitImage\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FilterResolver implements FilterResolverInterface
{
    protected $aliases = [];
    protected $filters = [];

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
     * {@inheritdoc}
     */
    public function add($filters, $name, $alias = null)
    {
        $alias = $alias ?: $name;
        $this->aliases[$alias] = $name;

        $filters = is_array($filters) ? $filters : [$filters];

        foreach ($filters as $filter) {
            $this->setFilter($filter, $name);
        }
    }

    /**
     * setFilter
     *
     * @param FilterInterface $filter
     * @param mixed $name
     *
     * @return void
     */
    protected function setFilter(FilterInterface $filter, $name)
    {
        $this->filters[$name][] = $filter;
    }
}
