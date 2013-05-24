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
class ImageCache implements CacheInterface
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

            $this->image->clean();
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

        if (file_exists($path = $this->getFilePath($id))) {
            $this->pool[$id] = $path;
            return true;
        }
        return false;
    }

    /**
     * put
     *
     * @param mixed $id
     * @param mixed $contents
     * @access public
     * @return mixed
     */
    public function put($id, $contents)
    {
        if (false === $this->has($id)) {
            file_put_contents($this->getFilePath($id), $contents);
        }
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
}
