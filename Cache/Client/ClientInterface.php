<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache\Client;

/**
 * @class ClientInterface
 * @package Thapp\Image
 * @version $Id$
 */
interface ClientInterface
{
    public function get($key);

    public function set($key, $content);

    public function has($key);

    public function delete($key);
}
