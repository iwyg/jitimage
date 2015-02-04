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

/**
 * @trait ModulateFilterTrait
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ModulateFilterTrait
{
    protected function parseOption($option, $value)
    {
        return (float)$value;
    }

    protected function getShortOpts()
    {
        return ['b' => 'brightness', 's' => 'satturation', 'h' => 'hue'];
    }
}
