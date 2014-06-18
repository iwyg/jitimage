<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Adapter;

use \Thapp\Image\Adapter\FlysystemCache as FsCache;
use \Thapp\Image\ProcessorInterface;
use \Thapp\Image\Cache\AbstractCache;
use \Thapp\JitImage\Resource\FlySystemCachedResource;
use \GrahamCampbell\Flysystem\Managers\FlysystemManager;

/**
 * @class FlysystemCache
 * @see CacheInterface
 *
 * @package Thapp\JitImage\Adapter
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class FlysystemCache extends FsCache
{
    /**
     * @param FlysystemManager $manager
     *
     * @access public
     */
    public function __construct(FlysystemManager $manager, $path = 'cache', $metaPath = null, $prefix = 'fly_')
    {
        $this->fs     = $manager;
        $this->path   = $path;
        $this->prefix = $prefix;
        $this->pool   = [];

        $this->setMetaPath($metaPath);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(ProcessorInterface $proc, $file)
    {
        return new FlysystemCachedResource($proc, $file);
    }
}
