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
    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);
        $image = $proc->getDriver();
        $color = ($hex = $this->getOption('c', null)) ?
            $image->getPalette()->getColor($hex) :
            null;

        $image->filter(new ImageRotate($this->getOption('d', 0), $color));
    }

    protected function parseOption($option, $value)
    {
        if ('c' === $option) {
            return hexdec(ltrim((string)$value, '#'));
        }

        return (float)$value;
    }

    protected function getShortOpts()
    {
        return ['d' => 'degree', 'c' => 'backgroundcolor'];
    }
}
