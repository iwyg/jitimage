<?php

/*
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Gd;

use Thapp\JitImage\ProcessorInterface;

/**
 * @class Greyscale
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Greyscale extends AbstractImagickFilter
{
    protected $availableOptions = [];

    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        if (!$image = $proc->getCurrentImage()) {
            return;
        }

        imagefilter($image->getGdResource(), IMG_FILTER_GRAYSCALE);
    }
}
