<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache\Client;

use Thapp\JitImage\Cache\CacheInterface;

/**
 * @class MemcachedClient
 * @see ClientInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class MemcachedClient implements ClientInterface
{
    private $memcached;

    /**
     * Constructor.
     *
     * @param \Memcached $memcached
     */
    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $client;
    }

    /**
     * getMemcached
     *
     * @return \Memcached
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $content, $expires = CacheInterface::EXPIRY_NONE)
    {
        $this->memcached->set($key, $content, CacheInterface::EXPIRY_NONE === $expires ? 0 : $expires);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        if (false === $this->memcached->get($key)) {
            return Memcached::RES_NOTFOUND !== $this->driver->getResultCode();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteKeys(array $keys)
    {
        return $this->memcached->deleteMulti($keys);
    }
}
