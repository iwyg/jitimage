<?php

/*
 * This File is part of the Thapp\JitImage\Loader package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Loader;

use Dropbox\Client;
use Dropbox\Exception as DboxException;
use Thapp\JitImage\Exception\SourceLoaderException;

/**
 * @class DropboxLoader
 *
 * @package Thapp\JitImage\Loader
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class DropboxLoader extends AbstractLoader
{
    private $client;
    private $prefix;

    public function __construct(Client $client, $prefix = null)
    {
        $this->client = $client;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        if (!$handle = $this->readStream($file)) {
            throw new SourceLoaderException(sprintf('Could not load source "%s".', $file));
        }

        if (!$resource = $this->validate($handle)) {
            throw new SourceLoaderException(sprintf('source "%s" is not an image', $file));
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($path)
    {
        return null !== $this->client->getMetaData($this->getPrefixed($path));
    }

    protected function getPrefixed($path)
    {
        if (0 === mb_strpos($path, '.')) {
            $path = mb_substr($path, 1);
        }

        $path = ltrim($path, '/');

        return '/' . ltrim(0 !== mb_strlen($path) ? ($this->prefix ?: '') . $path : ($this->prefix ?: ''), '/');
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $stream = tmpfile();

        if (null === $this->client->getFile($this->getPrefixed($path), $stream)) {
            fclose($stream);

            return false;
        }

        rewind($stream);

        return $stream;
    }
}
