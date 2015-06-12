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

use Thapp\Image\Color\Parser;

/**
 * @class FilterExpression
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FilterExpression
{
    private $expr;
    private $params;
    private $prefix;

    /**
     * Constructor.
     *
     * @param mixed $params
     * @param string $prefix
     */
    public function __construct($params, $prefix = 'filter')
    {
        $this->prefix = $prefix;
        $this->setExpression($params);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str = $this->compile();

        return $str;
    }

    public function __clone()
    {
        $this->expr = null;
        $this->params = null;
    }

    /**
     * Sets the filter expression
     *
     * @param mixed $expr array of parameters or string.
     *
     * @return void
     */
    public function setExpression($expr)
    {
        $this->expr = null;

        if (is_string($expr)) {
            if (0 === $pos = strpos($expr, $this->prefix.':')) {
                $expr = substr($expr, strlen($this->prefix.':'));
            }
        } elseif (!is_array($expr)) {
            throw new \InvalidArgumentException(
                sprintf('%s expects argument 1 to be a string or a array, %s given.', __METHOD__, gettype($expr))
            );
        }

        $this->params = $expr;
    }

    /**
     * Add a filter to the expression.
     *
     * @param string $filter
     * @param array $options
     *
     * @return void
     */
    public function addFilter($filter, array $options = [])
    {
        if (0 === strlen($filter)) {
            return;
        }

        $this->ensureArray();
        $this->params[$filter] = $options;
    }

    /**
     * Compile this expression to string
     *
     * @return string
     */
    public function compile()
    {
        if ($this->expr) {
            return $this->expr;
        }

        if (is_string($this->params)) {
            return $this->expr = $this->params;
        }

        return $this->expr = $this->compileParams();
    }

    /**
     * Transform this expression to an array
     *
     * @return array
     */
    public function toArray()
    {
        if (is_array($this->params)) {
            return $this->params;
        }

        if (0 === strlen($this->params)) {
            return $this->params = [];
        }

        return $this->transFormString($this->params);
    }

    /**
     * @see toArray
     *
     * @return array
     */
    public function all()
    {
        return $this->toArray();
    }

    /**
     * ensureArray
     *
     * @return array
     */
    private function ensureArray()
    {
        if (!is_array($this->params)) {
            $this->toArray();
        }
    }

    /**
     * Parse the input string expression
     *
     * @param string $str
     *
     * @return array
     */
    private function transformString($str)
    {
        $filters = [];

        foreach (explode(':', $str) as $filter) {

            if (0 === substr_count($filter, ';')) {
                $filters[$filter] = [];
                continue;
            }

            $opt = [];
            $fname = substr($filter, 0, $pos = strpos($filter, ';'));
            $options = substr($filter, $pos + 1);

            foreach (explode(';', $options) as $option) {
                list ($oname, $val) = $this->getOption($option);
                $opt[$oname] = $val;
            }

            $filters[$fname] = $opt;
        }

        return $this->params = $filters;
    }

    /**
     * getOption
     *
     * @param mixed $option
     *
     * @return void
     */
    private function getOption($option)
    {
        if (0 === substr_count($option, '=')) {
            $oname = $option;
            $val = null;
        } else {
            list ($oname, $val) = explode('=', $option);
        }

        return [$oname, $this->getOptionValue($val)];
    }

    /**
     * @return mixed
     */
    private function getOptionValue($val)
    {
        if (!is_string($val)) {
            return $val;
        }

        if (0 === strpos($val, '#')) {
            $val = substr($val, 1);
        }

        switch (true) {
            case 0 === strlen($val) || 'null' === $val:
                return null;
            case Parser::isHex($val):
                return hexdec(ltrim(Parser::normalizeHex($val), '#'));
            case is_numeric($val):
                if (0 !== substr_count($val, '.')) {
                    return (float)$val;
                } elseif (0 === strpos($val, '0x') || strlen((string)(int)$val) === strlen($val)) {
                    return $this->getNumVal($val);
                }
            case in_array($val, ['true', 'false']):
                return 'true' === $val ? true : false;
            default:
                return $val;
        }
    }

    /**
     * getNumVal
     *
     * @param string $val
     *
     * @return float|int|string
     */
    private function getNumVal($val)
    {
        if (0 === strpos((string)$val, '0x')) {
            return hexdec($val);
        }

        return (int)$val;
    }

    /**
     * @return string
     */
    private function compileParams()
    {
        $filters = [];

        foreach ($this->params as $fname => $options) {

            if (is_int($fname)) {
                $fname   = $options;
                $options = [];
            }

            array_push($filters, ':', $fname);

            $opts = [];

            if (empty($options)) {
                continue;
            }

            foreach ((array)$options as $key => $value) {
                $opts[] = sprintf('%s=%s', $key, $value);
            }

            $filters[] = ';' . implode(';', $opts);
        }

        array_shift($filters);

        return implode('', $filters);
    }
}
