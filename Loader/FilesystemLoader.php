<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Loader;

use Thapp\JitImage\Exception\SourceLoaderException;

/**
 * @class FilesystemLoader extends AbstractLoader
 * @see AbstractLoader
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class FilesystemLoader extends AbstractLoader
{
    /**
     * file
     *
     * @var string
     */
    protected $file;

    /**
     * src
     *
     * @var string
     */
    protected $source;

    /**
     * {@inheritdoc}
     *
     * @throws SourceLoaderException if the file could not be opened.
     * @throws SourceLoaderException if the file is not an image.
     *
     * @return Thapp\JitImage\Resource\FileResourceInterface
     */
    public function load($file)
    {
        if (!$handle = @fopen($file, 'rb')) {
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
    public function supports($file)
    {
        // prevent errors on unsupported stream wrappers:
        return is_string($file) && is_file($file) && stream_is_local($file);
    }
}
