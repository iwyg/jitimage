<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use Thapp\Image\Color\Parser;

/**
 * @class Parameters
 * @package Thapp\Image\Driver
 * @version $Id$
 */
class Parameters
{
    const P_SEPARATOR = '/';

    private $str;
    private $params;
    private $separator;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [], $separator = self::P_SEPARATOR)
    {
        $this->params = $params;
        $this->separator = $separator;
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->str = null;
        $this->params = [];
    }

    /**
     * setHeight
     *
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    public function setTargetSize($width = null, $height = null)
    {
        $this->str = null;
        $this->params['width']  = $width;
        $this->params['height'] = $height;
    }

    /**
     * setMode
     *
     * @param int $mode
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->str = null;
        $this->params['mode']  = (int)$mode;
    }

    /**
     * setGravity
     *
     * @param int $gravity
     *
     * @return void
     */
    public function setGravity($gravity = null)
    {
        $this->str = null;
        $this->params['gravity'] = $gravity;
    }

    /**
     * setBackground
     *
     * @param string $color
     *
     * @return void
     */
    public function setBackground($background = null)
    {
        $this->str = null;

        if (null !== $background && $this->isColor($background)) {
            $this->params['background'] = $background;
        }
    }

    /**
     * all
     *
     * @return array
     */
    public function all()
    {
        $params = array_merge(static::defaults(), $this->params);

        return static::sanitize(
            $params['mode'],
            $params['width'],
            $params['height'],
            $params['gravity'],
            $params['background']
        );
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * asString
     *
     * @return string
     */
    public function asString()
    {
        if (null === $this->str) {
            $this->str = implode($this->separator, array_filter(array_values($this->all()), function ($val) {
                return null !== $val;
            }));
        }

        return $this->str;
    }

    public function setFromString($str)
    {
        $this->str = null;
        $this->params = static::parseString($str, $this->separator);
    }

    /**
     * parseString
     *
     * @param string $paramString
     * @param string $separator
     *
     * @return array
     */
    public static function parseString($paramString, $separator = self::P_SEPARATOR)
    {
        $parts = array_pad(explode($separator, $paramString), 5, null);

        if (isset($parts[4]) && (!is_numeric($parts[0]) && !static::isHex($parts[4]))) {
            $parts[4] = null;
        }

        list ($mode, $width, $height, $gravity, $background) = array_map(function ($value, $key = null) {
            return is_numeric($value) ? (int)$value : ltrim($value, ' #');
        }, $parts);

        return static::sanitize($mode, $width, $height, $gravity, $background);
    }

    private static function parseBackground($background)
    {
        //$alpha = null;
        //if (is_string($background) && (5 === $len = strlen($alpha) || 8 === $len)) {
            //$alpha = substr($background, 0, 2);
            //$background = substr($background, 2);
        //}
    }

    /**
     * isColor
     *
     * @param mixed $color
     *
     * @return bool
     */
    protected function isColor($color)
    {
        return static::isHex($color);
    }

    /**
     * isHex
     *
     * @param mixed $color
     *
     * @return boolean
     */
    private static function isHex($color)
    {
        $color = ltrim($color, '#');

        return Parser::isHex($color);
    }

    /**
     * sanitize
     *
     * @param int $mode
     * @param int $width
     * @param int $height
     * @param int $gravity
     * @param string $background
     *
     * @access private
     * @return array
     */
    private static function sanitize($mode = null, $width = null, $height = null, $gravity = null, $background = null)
    {
        if (null === $mode) {
            $mode = 0;
        }

        if (2 !== $mode && 3 !== $mode) {
            $gravity = null;
        } elseif (null === $gravity) {
            $gravity = 5;
        }

        if ($mode !== 3) {
            $background = null;
        } elseif (null !== $background) {
            $background = (is_int($background) && !Parser::isHex((string)$background)) ?
                $background :
                hexdec(Parser::normalizeHex($background));
        }

        if (4 < $mode || 0 === $mode) {
            $height     = null;
            $gravity    = null;
        }

        if (0 == $mode) {
            $width = null;
        }

        $width  = ($mode !== 1 && $mode !== 2) ? $width : (int)$width;
        $height = ($mode !== 1 && $mode !== 2) ? $height : (int)$height;

        return compact('mode', 'width', 'height', 'gravity', 'background');
    }

    private static function defaults()
    {
        return ['mode' => null, 'width' => null, 'height' => null, 'gravity' => null, 'background' => null];
    }

    /**
     * fromString
     *
     * @param mixed $paramString
     * @param mixed $separator
     *
     * @access public
     * @return Parameters
     */
    public static function fromString($paramString, $separator = self::P_SEPARATOR)
    {
        return new static(static::parseString($paramString, $separator), $separator);
    }
}
