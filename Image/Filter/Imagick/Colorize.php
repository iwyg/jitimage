<?php

/*
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter\Imagick;

use Thapp\Image\Color\Hex;
use Thapp\JitImage\ProcessorInterface;
use Thapp\Image\Filter\Imagick\Colorize as ImageColorize;

/**
 * @class Greyscale
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Colorize extends AbstractImagickFilter
{
    protected static $shortOpts = ['c' => 'color'];

    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);
        $color = new Hex($this->getOption('c', 'ff00ff'));

        $filter = new ImageColorize($color);
        $filter->apply($proc->getCurrentImage());
    }

}
