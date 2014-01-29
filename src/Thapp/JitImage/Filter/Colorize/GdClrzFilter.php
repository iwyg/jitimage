<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Colorize package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Colorize;

use Thapp\JitImage\Filter\GdFilter;

/**
 * @class GdClrzFilter
 * @package vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Colorize
 * @version $Id$
 */
class GdClrzFilter extends GdFilter
{
    protected $availableOptions = ['c'];

    public function run()
    {
        list($r, $g, $b) = $this->hexToRgb($this->getOption('c', 'fff'));
        imagefilter($this->driver->getResource(), IMG_FILTER_CONTRAST, 1);
        imagefilter($this->driver->getResource(), IMG_FILTER_BRIGHTNESS, -12);
        imagefilter($this->driver->getResource(), IMG_FILTER_GRAYSCALE);
        $this->getOverlay($r, $g, $b);
    }

    protected function getOverlay($r, $g, $b)
    {
        extract($this->driver->getTargetSize());
        $image = $this->driver->getResource();
        $overlay = imagecreatetruecolor($width, $height);

        imagealphablending($image, true);
        imagelayereffect($image, IMG_EFFECT_OVERLAY);
        imagefilledrectangle($overlay, 0, 0, $width, $height, imagecolorallocatealpha($overlay, $r, $g, $b, 0));
        imagecopy($image, $overlay, 0, 0, 0, 0, imagesx($overlay), imagesy($overlay));
    }
}
