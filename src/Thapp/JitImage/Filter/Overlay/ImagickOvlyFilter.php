<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Overlay;

use \Imagick;
use \ImagickDraw;
use \ImagickPixel;
use Thapp\JitImage\Filter\ImagickFilter;
/**
 * @class ImagickOvlyFilter
 * @package vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay
 * @version $Id$
 */
class ImagickOvlyFilter extends ImagickFilter
{
    protected $availableOptions = ['c', 'a'];

    public function run()
    {
        extract($this->driver->getTargetSize());

        $image = $this->driver->getResource();

        $rgba    = implode(',', $this->hexToRgb($this->getOption('c', 'fff')));
        $alpha   = $this->getOption('a', '0.5');

        $overlay = new Imagick();
        $overlay->newImage($width, $height, new ImagickPixel(sprintf('rgba(%s,%s)', $rgba, $alpha)));
        $image->compositeImage($overlay, Imagick::COMPOSITE_OVER, 0, 0);
        //$this->driver->swapResource($overlay);
    }
}
