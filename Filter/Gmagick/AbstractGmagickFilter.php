<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Gmagick;

use Thapp\JitImage\Filter\AbstractFilter;
use Thapp\JitImage\ProcessorInterface;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractGmagickFilter extends AbstractFilter
{
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getCurrentImage() instanceof \Imagine\Gmagick\Image;
    }
}
