<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use \Thapp\Image\Processor;

/**
 * @class JitImageProcessor
 * @package \Thapp\JitImage
 * @version $Id$
 */
class JitImageProcessor extends Processor
{
    /**
     * @param int
     */
    private $quality;

    /**
     * {@inheritDoc}
     */
    public function load($source)
    {
        parent::load($source);

        $this->driver->setQuality($this->quality ?: 80);
    }

    /**
     * {@inheritDoc}
     */
    public function setQuality($quality)
    {
        $this->quality = (int)$quality;
    }
}
