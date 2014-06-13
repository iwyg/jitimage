<?php

/**
 * This File is part of the Thapp\JitImage\Adapter package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Adapter;

use \Thapp\Image\Cache\AbstractCache;
use \Thapp\Image\Resource\CachedResource;
use \GrahamCampbell\Flysystem\Managers\FlysystemManager;

/**
 * @class FlysystemCache implements CacheInterface
 * @see CacheInterface
 *
 * @package Thapp\JitImage\Adapter
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class FlysystemCache extends AbstractCache
{
    /**
     * fs
     *
     * @var mixed
     */
    protected $fs;

    /**
     * path
     *
     * @var mixed
     */
    protected $path;

    /**
     * @param FlysystemManager $manager
     *
     * @access public
     */
    public function __construct(FlysystemManager $manager, $path = 'cache', $prefix = 'fly_')
    {
        $this->fs     = $manager;
        $this->path   = $path;
        $this->prefix = $prefix;
        $this->pool   = [];
    }

    /**
     * get
     *
     * @param mixed $id
     * @param mixed $raw
     *
     * @access public
     * @return mixed
     */
    public function get($id, $raw = false)
    {
        return $raw ? $this->fs->get($this->getPath($id)) : $this->createResource($id);
    }

    /**
     * set
     *
     * @param string $id
     * @param string $contents
     *
     * @return void
     */
    public function set($id, $contents)
    {
        $this->fs->put($path = $this->getPath($id), $contents);

        $this->pool[$id] = $path;
    }

    /**
     * has
     *
     * @param mixed $id
     *
     * @access public
     * @return void
     */
    public function has($id)
    {
        if ($this->poolHas($id)) {
            return true;
        }

        return $this->fs->has($this->getPath($id));
    }


    /**
     * createResource
     *
     * @return mixed
     */
    private function createResource($id)
    {
        $path = $this->getPath($id);

        return new CachedResource(
            $path,
            $this->fs->read($path),
            $this->fs->getTimestamp($path),
            $this->mapMimeType($this->fs->read($path))
            //$this->fs->getMimetype($path)
        );
    }


    /**
     * @TODO: get rid of this shit
     */
    private function mapMimeType($content)
    {
        $byteA = bin2hex($content[0]);
        $byteB = bin2hex($content[1]);

        $charA = $content[0];
        $charB = $content[1];
        $charC = $content[2];
        $charD = $content[3];

        if ('ff' === $byteA && 'd8' === $byteB) {
            return 'image/jpeg';
        }

        if ('89' === $byteA && 'P' === $charB && 'N' === $charC && 'G' === $charD) {
            return 'image/png';
        }

        if ('G' === $charA && 'I' === $charB && 'F' === $charC) {
            return 'image/gif';
        }
    }

    /**
     * getPath
     *
     * @param mixed $id
     *
     * @return mixed
     */
    protected function getPath($id)
    {
        list ($base, $file) = $this->parseKey($id);

        $path = $this->path . '/' . $base . '/' . $file;

        return ltrim($path, '/') . '.img';
    }
}
