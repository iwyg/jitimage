<?php

/*
 * This File is part of the Thapp\JitImage\Image\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter;

use Thapp\JitImage\ProcessorInterface;
use Thapp\Image\Color\Hex;
use Thapp\Image\Filter\Rotate as ImageRotate;

/**
 * @class Rotate
 *
 * @package Thapp\JitImage\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Rotate extends AbstractFilter
{
    protected static $shortOpts = ['d' => 'degree', 'c' => 'backgroundcolor'];

    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);
        $color = ($hex = $this->getOption('c', null)) ? new Hex($hex) : null;

        $filter = new ImageRotate($this->getOption('d', 0), $color);

        return $filter->apply($proc->getCurrentImage());
    }
}
