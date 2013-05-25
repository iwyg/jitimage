<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter\GreyScale package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\GreyScale;

use Thapp\JitImage\Filter\GdFilter;

/**
 * Class: ImagickGsFilter
 *
 * @uses ImagickFilter
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class GdGsFilter extends GdFilter
{
    /**
     * run
     *
     * @access public
     * @return void
     */
    public function run()
    {
        imagefilter($this->driver->getResource(), IMG_FILTER_GRAYSCALE);
    }
}
