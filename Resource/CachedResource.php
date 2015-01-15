<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

use Thapp\JitImage\ProcessorInterface;

/**
 * @class CachedResource extends AbstractResource
 * @see AbstractResource
 *
 * @package Thapp\Image
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class CachedResource extends AbstractResource implements \Serializable
{
    protected $dimensions;

    /**
     * @param string $path
     * @param string $contents
     * @param int $lastModified
     * @param string $mime
     */
    public function __construct(ProcessorInterface $proc, $path = null)
    {
        $this->path = $path;
        $this->contents = $proc->getContents();

        $this->mimeType     = $proc->getMimeType();
        $this->lastModified = $proc->getLastModTime();
        $this->dimensions   = $proc->getTargetSize();
        $this->fresh = false;
    }

    public function getFileName()
    {
        return basename($this->path);
    }

    public function getContents()
    {
        return $this->contents = is_callable($this->contents) ? call_user_func($this->contents) : $this->contents;
    }

    /**
     * {@inheritdoc}
     */
    public function setContents($contents)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLastModified($time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setMimeType($type)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDimension()
    {
        return $this->dimensions;
    }

    /**
     * serialize
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
         'path'         => $this->path,
         'mimeType'     => $this->mimeType,
         'lastModified' => $this->lastModified,
         'dimensions'   => $this->dimensions
        ]);
    }

    /**
     * unserialize
     *
     * @param string $data
     *
     * @return CachedResource
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->path         = $data['path'];
        $this->mimeType     = $data['mimeType'];
        $this->lastModified = $data['lastModified'];
        $this->dimensions   = $data['dimensions'];

        $this->contents = $this->initContent();
    }

    /**
     * initContent
     *
     * @return \Closure
     */
    protected function initContent()
    {
        return function () {
            return file_get_contents($this->path);
        };
    }
}
