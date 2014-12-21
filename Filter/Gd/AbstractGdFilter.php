<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Gd;

use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Filter\AbstractFilter;

/**
 * @class AbstractGdFilter
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractGdFilter extends AbstractFilter
{
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getCurrentImage() instanceof \Imagine\Gd\Image;
    }
}
