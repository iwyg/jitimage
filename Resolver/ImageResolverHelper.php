<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

use \Thapp\Image\ProcessorInterface;
use \Thapp\JitImage\Resource\ImageResource;

/**
 * @trait ImageResolverHelper
 * @package Thapp\JitImage
 * @version $Id$
 */
trait ImageResolverHelper
{
    /**
     * getPath
     *
     * @return string
     */
    private function getPath($path, $source)
    {
        if (null === $path || null !== parse_url($source, PHP_URL_SCHEME)) {
            return $source;
        };

        if (null !== parse_url($path, PHP_URL_PATH)) {
            $slash = DIRECTORY_SEPARATOR;

            return rtrim($path, '\\\/') . $slash . strtr($source, ['/' => $slash]);
        }

        return $path . '/' . $source;
    }

    /**
     * @param ProcessorInterface $processor
     * @param string $source
     * @param array $params
     * @param \Thapp\Image\Cache\CacheInterface $cache
     * @param string $key
     *
     * @access private
     * @return ResourceInterface
     */
    private function applyProcessor(ProcessorInterface $processor, $source, array $params, $cache = null, $key = null)
    {
        $this->processor->load($source);
        $this->processor->process($params);

        if (null === $cache) {
            return $this->createResource($this->processor);
        }

        $cache->set($key, $this->processor->getContents());

        return $cache->get($key);
    }

    /**
     * createResource
     *
     * @param ProcessorInterface $processor
     *
     * @return ResourceInterface
     */
    private function createResource(ProcessorInterface $processor)
    {
        $resource = new ImageResource;

        $resource->setContents($processor->getContents());
        $resource->setFresh(!$processor->isProcessed());
        $resource->setLastModified($processor->getLastModTime());
        $resource->setMimeType($processor->getMimeType());

        // if the image was passed through, we can set a source path
        if (!$processor->isProcessed()) {
            $resource->setPath($processor->getSource());
        }

        return $resource;
    }
}
