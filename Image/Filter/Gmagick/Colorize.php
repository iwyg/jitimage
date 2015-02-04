<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter\Gmagick;

use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Image\Filter\ColorizeFilterTrait;
use Thapp\Image\Color\ColorInterface;
use Thapp\Image\Filter\Gmagick\Colorize as GmagickColorize;

/**
 * @class Greyscale
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Colorize extends AbstractGmagickFilter
{
    use ColorizeFilterTrait;

    /**
     * {@inheritdoc}
     */
    protected function newFilter(ColorInterface $color)
    {
        return new GmagickColorize($color);
    }
}
