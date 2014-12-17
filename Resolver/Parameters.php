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
 * @class Parameters
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class Parameters
{
    public function __construct($parameters)
    {
        $this->params = $parameters;
    }

    public function __clone()
    {
        $this->params = null;
    }

    public function addParameter($name, $value)
    {
        if (!static::isValidParam($name)) {
            return;
        }

        $this->params[$name] = $value;
    }

    public function compile()
    {
        if (is_string($this->expr)) {
            return $this->expr;
        }

        return $this->expr = $this->paramsToString();
    }

    public function toArray()
    {
        $params = $this->extractParams((string)$this->params);
    }

    private function paramsToString()
    {
        return implode('/', $this->filterParams((array)$this->params));
    }

    private function filterParams(array $params)
    {
        return array_filter(function ($value) {
            null !== $value;
        }, $params);
    }

    /**
     * extractParams
     *
     * @param string $params
     *
     * @return array
     */
    private function extractParams($params)
    {
        list ($mode, $width, $height, $gravity, $background) = array_map(function ($value, $key = null) {
            return is_numeric($value) ? (int)$value : $value;
        }, array_pad(explode('/', $params), 5, null));

        $width  = ($mode !== 1 && $mode !== 2) ? $width : (int)$width;
        $height = ($mode !== 1 && $mode !== 2) ? $height : (int)$height;

        return compact('mode', 'width', 'height', 'gravity', 'background');
    }

    private static function isValidParam($name)
    {
        return in_array($name, ['mode', 'width', 'height', 'gravity', 'background']);
    }
}
