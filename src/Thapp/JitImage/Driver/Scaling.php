<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Driver;

/**
 * Trait: Scaling
 *
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
trait Scaling
{
    /**
     * fitInBounds
     *
     * @param int $w maximum width
     * @param int $h maximum height
     * @param int $width
     * @param int $height
     *
     * @access protected
     * @return mixed
     */
    protected function fitInBounds($w, $h, $width, $height)
    {
        if ($width >= $w or $height >= $h) {

            $ratA = $this->ratio($width, $height);
            $ratM = min($ratA, $this->ratio($w, $h));

            $isR = $ratM === $ratA;

            $valW = (int)round($h * $ratM);
            $valH = (int)round($w / $ratA);

            list($width, $height) = $width <= $height ? [$valW, $isR ? $h : $valH] : [$isR ? $w : $valW, $valH];
        }

        return compact('width', 'height');
    }

    /**
     * fillArea
     *
     * @param int $w
     * @param int $h
     * @param int $width
     * @param int $height
     *
     * @access protected
     * @return void
     */
    protected function fillArea(&$w, &$h, $width, $height)
    {
        extract($this->getInfo());

        $ratio = $this->ratio($width, $height);

        $minW = min($w, $width);
        $minH = min($h, $height);
        $minB = min($w, $h);

        if ($minB === 0 or ($minW > $width and $minH > $height)) {
            return;
        }

        $ratioC = $this->ratio($w, $h);

        list($w, $h) = $ratio > $ratioC ? [(int)ceil($h * $ratio), $h] : [$w, (int)ceil($w / $ratio)];
    }

    /**
     * ratio
     *
     * @param int $width
     * @param int $height
     *
     * @access protected
     * @return float
     */
    protected function ratio($width, $height)
    {
        return (float)($width / $height);
    }

    /**
     * pixelLimit
     *
     * @param int   $width
     * @param int   $height
     * @param int   $limit
     *
     * @access protected
     * @return array $width and $height
     */
    protected function pixelLimit($width, $height, $limit)
    {
        $ratio  = $this->ratio($width, $height);
        $width  = (int)round(sqrt($limit * $ratio));
        $height = (int)floor($width / $ratio);

        return compact('width', 'height');
    }

    /**
     * percentualScale
     *
     * @param int   $width
     * @param int   $height
     * @param float $ratio
     *
     * @access protected
     * @return array $width and $height
     */
    protected function percentualScale($width, $height, $percent)
    {
        $ratio  = $this->ratio($width, $height);
        $width  = (int)(round($width * $percent) / 100);
        $height = (int)floor($width / $ratio);

        return compact('width', 'height');

    }

    /**
     * getCropCoordinates
     *
     * @param int $width   actual image width
     * @param int $height  actual image height
     * @param int $w       crop width
     * @param int $h       crop height
     * @param int $gravity crop position
     *
     * @access protected
     * @return array an array containing the crop coardinates x and y
     */
    protected function getCropCoordinates($width, $height, $w, $h, $gravity)
    {
        $x = $y = 0;

        switch ($gravity) {
            case 1: // GRAVITY_NORTHWEST
                break;
            case 3: // GRAVITY_NORTHEAST
                $x = ($width) - $w;
                break;
            case 2: // GRAVITY_NORTH
                $x = ($width / 2) - ($w / 2);
                break;
            case 4: // GRAVITY_WEST:
                $y = ($height / 2) - ($h / 2);
                break;
            case 5: // GRAVITY_CENTER
                $x = ($width / 2) - ($w / 2);
                $y = $height / 2  - ($h / 2);
                break;
            case 6: // GRAVITY_EAST
                $x = $width - $w;
                $y = ($height / 2)  - ($h / 2);
                break;
            case 7: // GRAVITY_SOUTHWEST
                $x = 0;
                $y = $height - $h;
                break;
            case 8: // GRAVITY_SOUTH
                $x = ($width / 2) - ($w / 2);
                $y = $height - $h;
                break;
            case 9: // GRAVITY_SOUTHEAST
                $x = $width - $w;
                $y = $height - $h;
                break;
        }

        $x = (int)ceil($x);
        $y = (int)ceil($y);

        return compact('x', 'y');
    }
}
