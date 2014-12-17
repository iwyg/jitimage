<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Image\Resource;

use \League\Flysystem\FilesystemInterface;

/**
 * @class FlysystemResource
 * @package Thapp\Image
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class FlysystemCachedResource extends CachedResource
{
    /**
     * fs
     *
     * @var FilesystemInterface
     */
    private $fs;

    /**
     * setFs
     *
     * @param FilesystemInterface $fs
     *
     * @return void
     */
    public function setFs(FilesystemInterface $fs)
    {
        $this->fs = $fs;
    }

    /**
     * initContent
     *
     * @return \Closure
     */
    protected function initContent()
    {
        return function () {
            return $this->fs->read($this->getPath());
        };
    }
}
