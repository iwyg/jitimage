<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Image\Cache;

use \Thapp\Image\ProcessorInterface;
use \Thapp\Image\Resource\NullResource;

/**
 * @class FilesystemCache implements CacheInterface
 * @see CacheInterface
 *
 * @package Thapp\Image\Cache
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class NullCache extends AbstractCache
{
    public function set($id, ProcessorInterface $proc)
    {
    }

    public function has($id)
    {
        return true;
    }

    public function get($id, $raw = false)
    {
        return new NullResource;
    }

    public function purge()
    {
    }

    public function delete($file)
    {
    }
}
