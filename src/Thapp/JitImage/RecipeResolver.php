<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

/**
 * @class RecipeResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class RecipeResolver
{
    /**
     * params
     *
     * @var array
     */
    private $params;

    /**
     * @param array $params
     *
     * @access public
     * @return mixed
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
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
        if (isset($this->params[$recipe])) {

            $parameter = $this->params[$recipe];
            list($parameters, $filter) = array_pad(explode(',', str_replace(' ', null, $parameter)), 2, null);

            return compact('parameters', 'filter');
        }
    }
}
