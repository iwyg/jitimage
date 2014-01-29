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

use Thapp\JitImage\Filter\GdFilter;

/**
 * @class GdOvlyFilter
 * @package vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay
 * @version $Id$
 */
class GdOvlyFilter extends GdFilter
{
    protected $availableOptions = ['c', 'a'];

    public function run()
    {
        list($r, $g, $b) = $this->hexToRgb($this->getOption('c', 'fff'));
        $overlay = $this->createOverlay($r, $g, $b, (float)$this->getOption('a', '0.5'));
        return null;
    }

    private function createOverlay($r, $g, $b, $alpha)
    {
        extract($this->driver->getTargetSize());
        $image = $this->driver->getResource();
        imagealphablending($image, true);
        $alpha = (int)(127 * 0.5);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocatealpha($image, $r, $g, $b, $alpha));
    }
}
