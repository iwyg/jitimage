<?php

/*
 * This File is part of the Thapp\JitImage\Imagine\Filter\Imagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Imagine\Filter\Imagick;

use Imagine\Image\Imagick\Image;
use Thapp\JitImage\ProcessorInterface;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\JitImage\Imagine\Filter\Imagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class AbstractImagickFilter implements FilterInterface
{
    public function supports(ProcessorInterface $proc)
    {
        $proc->getCurrentImage() instanceof Image;
    }
}
