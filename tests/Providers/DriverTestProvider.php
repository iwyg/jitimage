<?php

/**
 * This File is part of the Thapp\Test\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Test\JitImage\Providers;

/**
 * Trait: DriverTestProvider
 *
 *
 * @package Thapp\Test\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
trait DriverTestProvider
{
    /**
     * filterDataProvider
     *
     * @access public
     * @return array
     */
    public function filterDataProvider()
    {
        return [
            ['gs']
        ];
    }
    /**
     * cropScaleProvider
     *
     * @access public
     * @return array
     */
    public function cropScaleProvider()
    {
        return [
            [1, [
                [255, 0, 0], [0, 255, 0], [0, 0, 255]
            ]],
            [4, [
                [0, 255, 255], [255, 0, 255], [255, 255, 0]
            ]],
            [7, [
                [0, 0, 0], [255, 255, 255], [127, 127, 127]
            ]]
        ];
    }

    /**
     * imageTypeProvider
     *
     * @access public
     * @return array
     */
    public function imageTypeProvider()
    {
        return [
         [null,  'jpeg', 'image/jpeg'],
         ['jpg', 'jpeg', 'image/jpeg'],
         ['png', 'png', 'image/png'],
         ['gif', 'gif', 'image/gif']
        ];
    }

    /**
     * percentualResizeProvider
     *
     * @access public
     * @return array
     */
    public function percentualResizeProvider()
    {
        return [
            [200, 200, 100, [200, 200]],
            [200, 200, 50, [100, 100]],
            [200, 200, 25, [50, 50]],
            [500, 325, 20, [100, 65]],
            [325, 500, 20, [65, 100]]
        ];
    }

    /**
     * sizeRatioProvider
     *
     * @access public
     * @return array
     */
    public function sizeRatioProvider()
    {
        return [
            [200, 200, 1.0],
            [200, 144, (float)(200 / 144)],
            [144, 220, (float)(144 / 220)],
            [530, 445, (float)(530 / 445)]
        ];
    }
    /**
     * resizeParameterProvider
     *
     * @access public
     * @return array
     */
    public function resizeFilterParameterProvider()
    {
        return [
            /*
             * width, height, scale w, scale h, expected outcome
             */
            [200, 200, 100, 0, [100, 100]],
            [200, 200, 100, 100, [100, 100]],
            [200, 200, 400, 400, [400, 400]],
            [200, 200, 400, 600, [400, 600]],
            [200, 200, 400, 0, [400, 400]],
            [400, 350, 600, 0, [600, 525]],
            [400, 350, 0, 600, [685, 600]],
            [350, 400, 600, 0, [600, 685]]
        ];
    }

    public function pixelLimitProvider()
    {
        return [
            /*
             * width, height, scale w, scale h, expected outcome
             */
            [200, 300, 100000],
            [300, 200, 100000],
            [200, 200, 100000]
        ];

    }

    /**
     * resizeToFitFilterParameterProvider
     *
     * @access public
     * @return array
     */
    public function resizeToFitFilterParameterProvider()
    {
        return [
            /*
             * width, height, scale w, scale h, expected outcome
             */
            [200, 200, 100, 40,  [40,  40]],
            [200, 100, 400, 400, [200, 100]],
            [200, 100, 400, 600, [200, 100]],
            [200, 100, 600, 400, [200, 100]],
            [200, 100, 100, 100, [100, 50]],
            [100, 200, 400, 600, [100, 200]],
            [100, 200, 100, 100, [50,  100]],
            [331, 500, 200, 200, [132, 200]],
            [500, 331, 200, 200, [200, 132]],
            [750, 500, 200, 200, [200, 133]],
            [500, 750, 200, 200, [133, 200]],
        ];
    }

    /**
     * targetColors
     *
     * @access public
     * @return array
     */
    public function targetColors()
    {
        return [
          [1, 255, 0, 0],
          [2, 0, 255, 0],
          [3, 0, 0, 255],
          [4, 0, 255, 255],
          [5, 255, 0, 255],
          [6, 255, 255, 0],
          [7, 0, 0, 0],
          [8, 255, 255, 255],
          [9, 127, 127, 127]
        ];
    }

}
