<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter;

use Thapp\Image\Color\Parser;
use Thapp\JitImage\ProcessorInterface;
use Thapp\Image\Filter\Flip as FlipFilter;

/**
 * @class Rotate
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Flip extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);

        $image = $proc->getDriver();
        $image->filter(new FlipFilter($this->getOption('m', FlipFilter::FLIP_BOTH)));
    }

    protected function parseOption($option, $value)
    {
        return min(2, max(0, (int)$value));
    }

    protected function getShortOpts()
    {
        return ['m' => 'mode'];
    }
}
