<?php

/*
 * This File is part of the Thapp\JitImage\Resource package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

/**
 * @class FileResource
 *
 * @package Thapp\JitImage\Resource
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FileResource extends AbstractResource implements FileResourceInterface
{
    protected $handle;

    public function __construct($handle, $mime = null, $content = null)
    {
        $this->initialize($handle, $mime, $content);
    }

    public function getHandle()
    {
        return $this->handle;
    }

    protected function initialize($handle, $mime = null, $content = null)
    {
        $this->handle = $handle;
        $meta = stream_get_meta_data($handle);

        $this->setPath(isset($meta['uri']) ? $meta['uri'] : null);
        $this->setMimeType($this->findMimeType($meta, $mime));
    }

    protected function findMimeType(array $meta, $mime = null)
    {
        if (null === $mime && $this->isLocal()) {
            $mime = finfo_file($info = finfo_open(FILEINFO_MIME), $this->getPath());

            finfo_close($info);
        }

        return $mime;
    }
}
