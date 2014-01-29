<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

use FilesystemIterator;
use Thapp\JitImage\ImageInterface;
use Symfony\Component\Filesystem\Filesystem;
use Illuminate\Support\NamespacedItemResolver;

/**
 * Class: ImageCache
 *
 * @implements CacheInterface
 * @uses NamespacedItemResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImageCache extends NamespacedItemResolver implements CacheInterface
{
    /**
     * pool
     *
     * @var array
     */
    protected $pool = [];

    /**
     * path
     *
     * @var string
     */
    protected $path;

    /**
     * image
     *
     * @var \Thapp\JitImage\ImageInterface
     */
    protected $image;

    /**
     * create a new instance of \Thapp\JitImage\Cache\ImageCache
     *
     * @param \Thapp\JitImage\ImageInterface    $image
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param string                            $path       cache directory
     * @param int                               $permission octal r/w permssion
     *
     * @access public
     */
    public function __construct(ImageInterface $image, Filesystem $files, $path, $permission = 0777)
    {
        $this->image = $image;
        $this->files = $files;
        $this->setPath($path, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $raw = false)
    {
        if ($this->has($key)) {

            $this->image->close();
            $this->image->load($this->pool[$key]);

            return $raw ? $this->image->getImageBlob() : $this->image;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->pool)) {
            return true;
        }

        if ($this->files->exists($path = $this->getPath($key))) {
            $this->pool[$key] = $path;
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelPath($path)
    {
        return ltrim(substr($path, strlen($this->path)), '\\\/');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdFromUrl($url)
    {
        $parts = preg_split('~/~', $url, -1, PREG_SPLIT_NO_EMPTY);
        return implode('.', array_slice($parts, count($parts) >= 2 ? -2 : -1));
    }

    /**
     * {@inheritdoc}
     */
    public function createKey($src, $fingerprint = null, $prefix = 'io', $suffix = 'file')
    {
        return sprintf(
            '%s.%s%s%s',
            substr(hash('sha1', $src), 0, 8),
            $prefix,
            $this->pad($src, $fingerprint),
            $this->pad($src, $suffix, 3)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $contents)
    {
        if (false === $this->has($key)) {
            $this->files->dumpFile($this->realizeDir($key), $contents);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        try {
            foreach (new FilesystemIterator($this->path, FilesystemIterator::SKIP_DOTS) as $file) {
                $this->files->remove($file);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $id = $this->createKey($key);
        $dir = substr($id, 0, strpos($id, '.'));

        if ($this->files->exists($dir = $this->path . '/' . $dir)) {
            $this->files->remove($dir);
        }
    }

    /**
     * Get the full filepath of an cached item
     *
     * @param string $key cache key
     *
     * @access protected
     * @return string
     */
    protected function getFilePath($id)
    {
        return sprintf('%s/%s', $this->path, $id);
    }

    /**
     * Creates a cache subdirectory if necessary.
     *
     * @param  string $key the cache key
     *
     * @access protected
     * @return string cache file path
     */
    protected function realizeDir($key)
    {
        $path = $this->getPath($key);

        if (!$this->files->exists($dir = dirname($path))) {
            $this->files->mkdir($dir);
        }

        return $path;
    }

    /**
     * Get the cachedirectory from a cache key.
     *
     * @param string $key
     *
     * @access protected
     * @return string the dirctory path of the cached item
     */
    protected function getPath($key)
    {
        $parsed = $this->parseKey($key);

        array_shift($parsed);

        list ($dir, $file) = $parsed;
        return sprintf('%s/%s/%s', $this->path, $dir, $file);
    }

    /**
     * Appends and hash a string with another string.
     *
     * @param string $src
     * @param string $pad
     * @param int    $len
     *
     * @access protected
     * @return string
     */
    protected function pad($src, $pad, $len = 16)
    {
        return substr(hash('sha1', sprintf('%s%s', $src, $pad)), 0, $len);
    }

    /**
     * set the path to the cache directory
     *
     * @param string $path path to cache directory
     * @param int    $permission octal permission level
     *
     * @access protected
     * @return void
     */
    protected function setPath($path, $permission)
    {
        if (true !== $this->files->exists($path)) {
            $this->files->mkdir($path, $permission, true);
        }
        $this->path = $path;
    }
}
