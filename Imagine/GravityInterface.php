<?php

/*
 * This File is part of the Thapp\Image\Metrics package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Imagine;

use Imagine\Image\BoxInterface;

/**
 * @interface GravityInterface
 *
 * @package Thapp\Image\Metrics
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface GravityInterface
{
    const GRAVITY_NORTHWEST = 1;
    const GRAVITY_NORTHEAST = 2;
    const GRAVITY_NORTH = 3;
    const GRAVITY_WEST = 4;
    const GRAVITY_CENTER = 5;
    const GRAVITY_EAST = 6;
    const GRAVITY_SOUTHWEST = 7;
    const GRAVITY_SOUTH = 8;
    const GRAVITY_SOUTHEAST = 9;

    /**
     * getMode
     *
     * @return int
     */
    public function getMode();

    public function getPoint(BoxInterface $source, BoxInterface $target);
}
