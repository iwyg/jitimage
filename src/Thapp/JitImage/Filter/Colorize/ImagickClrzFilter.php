<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Colorize;

use \Imagick;
use \ImagickDraw;
use \ImagickPixel;
use Thapp\JitImage\Filter\ImagickFilter;

/**
 * @class ImOvlyFilter
 * @package vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay
 * @version $Id$
 */
class ImagickClrzFilter extends ImagickFilter
{
    protected $availableOptions = ['c'];

    public function run()
    {
        extract($this->driver->getTargetSize());

        $image = $this->driver->getResource();
        $rgba    = implode(',', $this->hexToRgb($this->getOption('c', 'fff')));

        $overlay = new Imagick();
        $overlay->newImage($width, $height, new ImagickPixel(sprintf('rgb(%s)', $rgba)));
        $image->compositeImage($overlay, Imagick::COMPOSITE_COLORIZE, 0, 0);
    }
}

