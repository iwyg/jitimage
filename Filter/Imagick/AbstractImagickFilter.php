<?php

/*
 * This File is part of the Thapp\JitImage\Filter\Imagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Imagick;

use Thapp\JitImage\Filter\AbstractFilter;
use Thapp\JitImage\ProcessorInterface;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\JitImage\Filter\Imagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractImagickFilter extends AbstractFilter
{
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getCurrentImage() instanceof \Imagine\Imagick\Image;
    }
}
