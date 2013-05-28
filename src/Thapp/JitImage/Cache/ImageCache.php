<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Cache package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

use Thapp\JitImage\ImageInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\NamespacedItemResolver;

/**
 * Class: ImageCache
 *
 * @implements CacheInterface
 *
 * @package
 * @version
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
     * @var mixed
     */
    protected $image;

    /**
     * __construct
     *
     * @param mixed $path
     * @access public
     * @return mixed
     */
    public function __construct(ImageInterface $image, Filesystem $files, $path, $permission = 0777)
    {
        $this->image = $image;
        $this->files = $files;
        $this->setPath($path, $permission);
    }

    /**
     * setPath
     *
     * @param mixed $path
     * @param mixed $permission
     * @access protected
     * @return mixed
     */
    protected function setPath($path, $permission)
    {
        if (true !== $this->files->exists($path)) {
            $this->files->makeDirectory($path, $permission);
        }
        $this->path = $path;
    }

    /**
     * get
     *
     * @param string $id  cached id
     * @param bool   $raw whather to return the contents or an image object
     * @access public
     * @return mixed
     */
    public function get($id, $raw = false)
    {
        if ($this->has($id)) {

            $this->image->close();
            $this->image->load($this->pool[$id]);

            return $raw ? $this->image->getImageBlob() : $this->image;
        }
    }

    /**
     * has
     *
     * @param mixed $id
     * @access public
     * @return mixed
     */
    public function has($id)
    {
        if (array_key_exists($id, $this->pool)) {
            return true;
        }

        if ($this->files->exists($path = $this->getPath($id))) {
            $this->pool[$id] = $path;
            return true;
        }

        return false;
    }

    /**
     * getRelPath
     *
     * @param mixed $path
     * @access public
     * @return mixed
     */
    public function getRelPath($path)
    {
        return ltrim(substr($path, strlen($this->path)), '\\\/');
    }

    /**
     * getIdFromUrl
     *
     * @param mixed $path
     * @access public
     * @return mixed
     */
    public function getIdFromUrl($url)
    {
        $parts = preg_split('~/~', $url, -1, PREG_SPLIT_NO_EMPTY);
        return implode('.', array_slice($parts, count($parts) >= 2 ? -2 : -1));
    }

    /**
     * createKey
     *
     * @param string $src
     * @param string $fingerprint
     * @param string $prefix
     * @param string $suffix
     * @access public
     * @return string
     */
    public function createKey($src, $fingerprint = null, $prefix = 'io',  $suffix = 'f')
    {
        return sprintf('%s.%s_%s.%s', substr(hash('sha1', $src), 0, 8), $prefix, $this->pad($src, $fingerprint), $suffix);
    }

    /**
     * put
     *
     * @param mixed $id
     * @param mixed $contents
     * @access public
     * @return void
     */
    public function put($id, $contents)
    {
        if (false === $this->has($id)) {
            $this->files->put($this->realizeDir($id), $contents);
        }
    }

    /**
     * create a directory if necessary
     *
     * @param  string $key
     *
     * @access protected
     * @return string cache file path
     */
    protected function realizeDir($key)
    {
        $path = $this->getPath($key);

        if (!$this->files->exists($dir = dirname($path))) {
            $this->files->makeDirectory($dir);
        }

        return $path;
    }

    /**
     * getPath
     *
     * @param mixed $key
     * @access protected
     * @return mixed
     */
    protected function getPath($key)
    {
        list ($ns, $dir, $file) = $this->parseKey($key);
        return sprintf('%s/%s/%s', $this->path, $dir, $file);
    }

    /**
     * pad
     *
     * @param mixed $src
     * @param mixed $pad
     * @access protected
     * @return mixed
     */
    protected function pad($src, $pad)
    {
        return substr(hash('sha1', sprintf('%s%s', $src, $pad)), 0, 16);
    }


    /**
     * getFilePath
     *
     * @access protected
     * @return mixed
     */
    protected function getFilePath($id)
    {
        return sprintf('%s/%s', $this->path, $id);
    }

    /**
     * purge
     *
     * @access public
     * @return void
     */
    public function purge()
    {
        try {
            foreach ($this->files->directories($this->path) as $directory) {
                $this->files->deleteDirectory($directory);
            }
        } catch (\Exception $e) {}
    }

    /**
     * delete
     *
     * @param mixed $src
     * @access public
     * @return mixed
     */
    public function delete($id)
    {
        $id = $this->createKey($id);
        $dir = substr($id, 0, strpos($id, '.'));

        if ($this->files->exists($dir = $this->path . '/' . $dir)) {
            $this->files->deleteDirectory($dir);
        }
    }
}
