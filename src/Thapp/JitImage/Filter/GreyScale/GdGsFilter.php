<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\GreyScale;

use Thapp\JitImage\Filter\GdFilter;

/**
 * Class: GdGsFilter
 *
 * @uses GdFilter
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class GdGsFilter extends GdFilter
{

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        imagefilter($this->driver->getResource(), IMG_FILTER_GRAYSCALE);
    }
}
