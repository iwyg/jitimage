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
use Thapp\Image\Driver\ImageInterface;
use Thapp\Image\Filter\Format as ImageFormat;

/**
 * @class Rotate
 *
 * @package Thapp\JitImage\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Convert extends AbstractFilter
{
    protected static $shortOpts = ['f' => 'format'];

    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);

        $filter = new ImageFormat($this->getOption('f', 'jpeg'));
        $filter->apply($proc->getCurrentImage());
    }
}
