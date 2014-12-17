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
    public function set($id, $content)
    {
        $this->memcached->set($id, $content, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->memcached->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return (bool)$this->memcached->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->memcached->delete($id);
    }
}
