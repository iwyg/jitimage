<?php

/*
 * This File is part of the Thapp\JitImage\Image\Filter\Imagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter\Gd;

use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Filter\AbstractFilter;
use Thapp\JitImage\Filter\FilterInterface;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\JitImage\Image\Filter\Imagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractGdFilter extends AbstractFilter implements FilterInterface
{
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getDriver() instanceof \Thapp\Image\Driver\Gd\Image;
    }
}
