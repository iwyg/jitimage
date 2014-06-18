<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

use \Thapp\Image\Resource\CachedResource;
use \GrahamCampbell\Flysystem\Managers\FlysystemManager;

/**
 * @class FlySystemCachedResource
 * @see CachedResource
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class FlysystemCachedResource extends CachedResource
{
    private $fs;

    public function setFs(FlysystemManager $fs)
    {
        $this->fs = $fs;
    }

    protected function initContent()
    {
        return function () {
            return $this->fs->read($this->getPath());
        };
    }
}
