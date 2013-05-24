<?php

/**
 * This File is part of the Thapp\JitImage\Filter\Oval package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Circle;

use \Imagick;
use \ImagickDraw;
use \ImagickPixel;
use Thapp\JitImage\Filter\ImagickFilter;

/**
 * Class: ImagickCircFilter
 *
 * @uses ImagickFilter
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImagickCircFilter extends ImagickFilter
{

    /**
     * run
     *
     * @access public
     * @return mixed
     */
    public function run()
    {
        $this->driver->setOutPutType('png');

        extract($this->driver->getTargetSize());

        list($ox, $oy, $px, $py) = $this->getCoordinates($width, $height);

        $image = $this->driver->getResource();

        $image->setImageFormat('png');
        $image->setImageMatte(false);

        $mask = clone $image;
        $mask->setImageMatte(false);

        $mask->thresholdImage(-1);
        $mask->negateImage(false);

        $circle = new ImagickDraw();
        $circle->setFillColor('white');
        $circle->circle($ox, $oy, $px, $py);

        $mask->drawImage($circle);

        $image->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
    }

    /**
     * getCoordinates
     *
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return mixed
     */
    protected function getCoordinates($width, $height)
    {
        $max = (int)ceil(max($width, $height) / 2);
        $min = (int)ceil(min($width, $height) / 2);


        return $width > $height ?
            [$max, $min, $max, $this->getOption('o', 1)]:
            [$min, $max, $this->getOption('o', 1), $max];
    }
}

