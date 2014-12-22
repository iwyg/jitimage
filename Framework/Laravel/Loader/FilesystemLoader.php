<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Loader;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * @class FilesystemLoader
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class FilesystemLoader extends AbstractLoader
{
    /**
     * files
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Constructor.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        if (!$handle = $this->loadResource($file)) {
            throw new SourceLoaderException(sprintf('Could not load file "%s".', $file));
        }

        if (!$resource = $this->validate($handle)) {
            throw new SourceLoaderException(sprintf('File "%s" is not an image.', $file));
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource)
    {
        return $this->files->exists($source);
    }

    /**
     * loadResource
     *
     * @param string$file
     *
     * @return resource
     */
    protected function loadResource($file)
    {
        if (!$contents = $this->files->get($file)) {
            return false;
        }

        $resource = tmpfile();

        fwrite($resource, $contents);
        rewind($resource);

        return $resource;
    }
}
