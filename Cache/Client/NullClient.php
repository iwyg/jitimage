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

class NullClient implements ClientInterface
{
    private $file;
    private $pool;
    private $persist;

    public function __construct($persist = false, $file = null)
    {
        $this->init($persist, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function set($id, $content)
    {
        $this->pool[$id] = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->has($id) ? $this->pool[$id] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->pool[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        unset($this->pool[$id]);
    }

    /**
     * init
     *
     * @param mixed $persist
     * @param mixed $file
     *
     * @return void
     */
    private function init($persist, $file)
    {
        $this->file    = $file;
        $this->persist = (bool)$persist;

        $this->pool = [];

        if ($persist && is_file($file)) {
            $this->pool = unserialize(file_get_contents($file));
        } elseif ($persist && !is_file($file)) {
            touch($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        if ($this->persist) {
            file_put_contents($this->file, serialize($this->pool));
        }
    }
}
