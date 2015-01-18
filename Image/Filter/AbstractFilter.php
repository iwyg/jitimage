<?php

/*
 * This File is part of the Thapp\JitImage\Imagine\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter;

use Thapp\Image\Driver\ImageInterface;
use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Filter\AbstractFilter as BaseFilter;

/**
 * @class AbstractFilter
 *
 * @package Thapp\JitImage\Imagine\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getCurrentImage() instanceof ImageInterface;
    }

}
