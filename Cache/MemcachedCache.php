<?php

/**
 * This File is part of the Image\Cache package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Image\Cache;

use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Resource\CacheClientResource;
use Thapp\JitImage\Cache\Client\MemcachedClient;

/**
 * @class MemcachedCache
 * @package \Users\malcolm\www\image\src\Thapp\Image\Cache
 * @version $Id$
 */
class MemcachedCache extends AbstractCache
{
    protected $id;
    protected $changes;
    protected $client;
    protected $resources;
    protected $pool;

    public function __construct(MemcachedClient $client, $id = 'image')
    {
        $this->changes = false;

        $this->client = $client;

        $this->id = $id;

        $this->initialize();
    }

    public function has($key)
    {
        list ($prefix, $id) = array_pad(explode('.', $key), 2, null);

        return isset($this->pool[$prefix][$id]);
    }

    public function get($key, $raw = false)
    {
        list ($prefix, $id) = explode('.', $key);

        $resource = $this->pool[$prefix][$id];

        $resource->setClient($this->client);
        $resource->setId($key);

        return $resource;
    }

    public function set($key, ProcessorInterface $proc)
    {
        $this->changes = true;

        list ($prefix, $id) = explode('.', $key);

        $this->pool[$prefix][$id] = $this->createResource($proc, $key, $prefix, $id);
    }

    public function purge()
    {
        $this->changes = true;

        unset($this->pool);

        $this->pool = [];
    }

    public function delete($image)
    {
        $this->changes = true;

        list ($prefix, $id) = explode('.', $this->createKey($image));

        unset($this->pool[$id]);
    }

    private function createResource($proc, $key, $prefix, $id)
    {
        $resource = new CacheClientResource($proc, $prefix . '/'. $id . '.' . $proc->getFileFormat());

        $resource->setClient($this->client);

        $resource->setId($key);

        $this->client->set($key, $proc->getContents(), 0);

        return $resource;
    }

    private function initialize()
    {
        $this->pool = $this->client->get($this->id) ?: [];
    }

    public function __destruct()
    {
        if ($this->changes) {
            $this->flushCache();
        }
    }

    private function flushCache()
    {
        $this->client->set($this->id, $this->pool);
    }
}
