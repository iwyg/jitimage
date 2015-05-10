<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

/**
 * @class ColorizeFilterTrait
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ColorizeFilterTrait
{
    /**
     * {@inheritdoc}
     */
    protected function parseOption($option, $value)
    {
        return (int)$value;
    }

    protected function getShortOpts()
    {
        return ['c' => 'color'];
    }
}
