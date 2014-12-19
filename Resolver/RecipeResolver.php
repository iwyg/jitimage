<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

/**
 * @class RecipeResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class RecipeResolver implements RecipeResolverInterface
{
    /**
     * params
     *
     * @var array
     */
    private $recipes;

    /**
     * @param array $params
     *
     * @access public
     * @return mixed
     */
    public function __construct(array $params = [])
    {
        $this->set($params);
    }

    /**
     * set
     *
     * @param array $recipes
     *
     * @return void
     */
    public function set(array $recipes) {
        foreach ($recipes as $recipe => $params) {
            if (2 !== count($params)) {
                continue;
            }

            $this->recipes[trim($recipe, '/')] = $params;
        }
    }

    /**
     * resolve
     *
     * @param mixed $recipe
     *
     * @access public
     * @return mixed
     */
    public function resolve($recipe)
    {
        if (!isset($this->recipes[$recipe = trim($recipe, '/')])) {
            return;
        }

        list($path, $parameter) = $this->recipes[$recipe];
        list($parameters, $filter) = array_pad(explode(',', str_replace(' ', null, $parameter)), 2, null);

        $filter = 0 === strpos($filter, 'filter:') ? substr($filter, 7) : $filter;

        return [$path, $parameters, $filter];
    }
}
