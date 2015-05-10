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

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;

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
    public function set(array $recipes)
    {
        foreach ($recipes as $recipe => $params) {
            if (2 > $count = count($params)) {
                continue;
            }

            if (!$args = $this->getRecipeArgs($params)) {
                continue;
            }

            list ($path, $parameters, $filters) = $args;

            $this->add($recipe, $path, $parameters, $filters);
        }
    }

    /**
     * add
     *
     * @param mixed $recipe
     * @param mixed $path
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return void
     */
    public function add($recipe, $path, Parameters $params, FilterExpression $filters = null)
    {
        $this->recipes[trim($recipe, '/')] = [$path, $params, $filters];
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

        return $this->recipes[$recipe];

        //list($path, $parameter) = $this->recipes[$recipe];
        //list($parameters, $filter) = array_pad(explode(',', str_replace(' ', null, $parameter)), 2, null);

        //$filter = 0 === strpos($filter, 'filter:') ? substr($filter, 7) : $filter;

        //return [$path, $parameters, $filter];
    }

    private function getRecipeArgs(array $params)
    {
        if (is_string($params[1])) {
            list($parameters, $filter) = array_pad(explode(',', str_replace(' ', null, $params[1])), 2, null);
            return [$params[0], Parameters::fromString($parameters), $filter ? new FilterExpression($filter) : null];
        }

        if ($params[1] instanceof Parameters) {
            return [$params[0], $params[1], isset($params[2]) ? $params[2] : null];
        }

        return false;
    }
}
