<?php

/**
 * This File is part of the Thapp\JitImage package
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
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImagickCircFilter extends ImagickFilter
{

    protected $availableOptions = ['o'];
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        extract($this->driver->getTargetSize());

        $image = $this->driver->getResource();

        $mask = new Imagick();
        $mask->newImage($width, $height, 'transparent');

        $mask->thresholdImage(-1);
        $mask->negateImage(1);
        $mask->setImageMatte(1);

        $circle = $this->makeCircle($width, $height);

        $mask->drawImage($circle);
        $mask->gammaImage(2.2);

        $image->setImageMatte(1);

        $image->setImageBackgroundColor('white');
        $image->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
        $image->setImageFormat('gif' === $this->driver->getOutputType() ? 'gif' : 'png');
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

    /**
     * makeCircle
     *
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return mixed
     */
    protected function makeCircle($width, $height)
    {
        list($ox, $oy, $px, $py) = $this->getCoordinates($width, $height);

        $circle = new ImagickDraw();
        $circle->setFillColor('white');
        $circle->circle($ox, $oy, $px, $py);

        return $circle;
    }

}
