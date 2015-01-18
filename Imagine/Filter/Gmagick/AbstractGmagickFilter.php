<?php

/*
 * This File is part of the Thapp\JitImage\Imagine\Filter\Gmagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Imagine\Filter\Gmagick;

use Imagine\Image\Gmagick\Image;
use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Filter\FilterInterface;

/**
 * @class AbstractGmagickFilter
 *
 * @package Thapp\JitImage\Imagine\Filter\Gmagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class AbstractGmagickFilter
{
    public function supports(ProcessorInterface $proc)
    {
        $proc->getCurrentImage() instanceof Image;
    }
}
