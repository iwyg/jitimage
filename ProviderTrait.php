<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

/**
 * @trait SilexProviderTrait
 * @package Thapp\JitImage
 * @version $Id$
 */
trait ProviderTrait
{
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

    /**
     * getDefaultCache
     *
     * @param string $path
     *
     * @return \Thapp\Image\Cache\CacheInterface
     */
    private function getDefaultCache($path)
    {
        return new \Thapp\Image\Cache\FilesystemCache(
            $path,
            storage_path() . '/jitimage/meta'
        );
    }
}
