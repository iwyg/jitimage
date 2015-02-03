<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Imagine;

use Imagine\Image\BoxInterface;

/**
 * @class GravityInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface GravityInterface
{
    /**
     * getMode
     *
     * @return int
     */
    public function getMode();

    /**
     * getPoint
     *
     * @param BoxInterface $source
     * @param BoxInterface $target
     *
     * @return Imagine\Image\PointInterface
     */
    public function getPoint(BoxInterface $source, BoxInterface $target);
}
