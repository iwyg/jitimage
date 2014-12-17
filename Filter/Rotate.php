<?php

/*
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

use Thapp\JitImage\ProcessorInterface;
use Imagine\Filter\Basic\Rotate as RtFilter;

/**
 * @class Rotate
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Rotate extends AbstractFilter
{
    protected $availableOptions = ['d', 'c'];

    /**
     * apply
     *
     * @param ProcessorInterface $proc
     *
     * @return void
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);
        $image = $proc->getCurrentImage();

        if (null !== $color = $this->getOption('c')) {
            $c = 0 === strpos((string)$color, '0x') ? hexdec($color) : $this->hexToRgb((string)$color);
            $color = $image->palette()->color($c);
        }

        $filter = new RtFilter((int)$this->getOption('d', 0), $color);
        $filter->apply($image);
    }
}
