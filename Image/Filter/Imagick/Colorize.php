<?php

/*
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter\Imagick;

use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Image\Filter\ColorizeFilterTrait;
use Thapp\Image\Color\ColorInterface;
use Thapp\Image\Filter\Imagick\Colorize as ImagickColorize;

/**
 * @class Greyscale
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Colorize extends AbstractImagickFilter
{
    use ColorizeFilterTrait;

    /**
     * {@inheritdoc}
     */
    protected function newFilter(ColorInterface $color)
    {
        return new ImagickColorize($color);
    }
}
