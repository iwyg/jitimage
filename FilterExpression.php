<?php

/**
 * This File is part of the Thapp\Image\Filter package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

/**
 * @class FilterExpression
 * @package Thapp\Image\Filter
 * @version $Id$
 */
class FilterExpression
{
    /**
     * expr
     *
     * @var string
     */
    private $expr;

    /**
     * params
     *
     * @var array
     */
    private $params;

    /**
     * Constructor.
     *
     * @param mixed $params
     */
    public function __construct($params)
    {
        $this->params = $params;
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

    public function setExpression($expr)
    {
        $this->expr = null;
        $this->params = $expr;
    }

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

        switch (true) {
            case 0 === strlen($val) || 'null' === $val:
                return null;
            case is_numeric($val):
                return $this->getNumVal($val);
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
        if (0 !== substr_count($val, '.')) {
            return (float)$val;
        } elseif (0 === strpos($val, '0x')) {
            return hexdec($val);
        }

        return $val;
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
