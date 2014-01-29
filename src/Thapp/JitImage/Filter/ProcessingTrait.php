<?php

/**
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

/**
 * @trait ProcessingTrait
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
trait ProcessingTrait
{
    public function hexToRgb($hex)
    {
        if (3 === ($len = strlen($hex))) {
            $rgb = str_split($hex);
            list($r, $g, $b) = $rgb;
            $rgb = [hexdec($r.$r), hexdec($g.$g), hexdec($b.$b)];
        } elseif (6 === $len) {
            $rgb = str_split($hex, 2);
            list($r, $g, $b) = $rgb;
            $rgb = [hexdec($r), hexdec($g), hexdec($b)];
        } else {
            throw new \InvalidArgumentException(sprintf('invalid hex value %s', $hex));
        }
        return $rgb;
    }
}
