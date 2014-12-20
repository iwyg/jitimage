<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Common;

/**
 * @trait ProviderHelperTrait
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ProviderHelperTrait
{
    /**
     * getImagineClass
     *
     * @param string $driver
     *
     * @return string
     */
    private function getImagineClass($driver)
    {
        switch ($driver) {
            case 'gd':
                return '\Imagine\Gd\Imagine';
            case 'imagick':
                return '\Imagine\Imagick\Imagine';
            case 'gmagick':
                return '\Imagine\Gmagick\Imagine';
            default:
                break;
        }

        throw new \InvalidArgumentException('Invalid driver "'. $driver .'".');
    }

    /**
     * getPathRegexp
     *
     * @return array
     */
    private function getPathRegexp()
    {
        return [
            '/{params}/{source}/{filter}',
            '([5|6](\/\d+){1}|[0]|[1|4](\/\d+){2}|[2](\/\d+){3}|[3](\/\d+){3}\/?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})?)',
            '((([^0-9A-Fa-f]{3}|[^0-9A-Fa-f]{6})?).*?.(?=(\/filter:.*)?))',
            '(filter:.([^\/])*)'
        ];
    }
}
