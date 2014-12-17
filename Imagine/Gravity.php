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

use Imagine\Image\Point;
use Imagine\Image\BoxInterface;

/**
 * @class Gravity
 *
 * @package Thapp\Image\Metrics
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Gravity implements GravityInterface
{
    /**
     * Constructor
     *
     * @param int $mode
     */
    public function __construct($mode)
    {
        $this->mode = max(1, min(9, (int)$mode));
    }

    public function getMode()
    {
        return $this->mode;
    }

    /**
     * getPoints
     *
     * @param BoxInterface $source
     * @param BoxInterface $target
     *
     * @return void
     */
    public function getPoint(BoxInterface $source, BoxInterface $target)
    {
        return $this->getCropFromGravity($source, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function getCropFromGravity(BoxInterface $source, BoxInterface $box)
    {
        $x = $y = 0;

        $width  = $source->getWidth();
        $height = $source->getHeight();

        $w = $box->getWidth();
        $h = $box->getHeight();

        switch ($this->getMode()) {
            case GravityInterface::GRAVITY_NORTHWEST:
                break;
            case GravityInterface::GRAVITY_NORTHEAST:
                $x = ($width) - $w;
                break;
            case GravityInterface::GRAVITY_NORTH:
                $x = ($width / 2) - ($w / 2);
                break;
            case GravityInterface::GRAVITY_WEST:
                $y = ($height / 2) - ($h / 2);
                break;
            case GravityInterface::GRAVITY_CENTER:
                $x = ($width / 2) - ($w / 2);
                $y = $height / 2  - ($h / 2);
                break;
            case GravityInterface::GRAVITY_EAST:
                $x = $width - $w;
                $y = ($height / 2)  - ($h / 2);
                break;
            case GravityInterface::GRAVITY_SOUTHWEST:
                $x = 0;
                $y = $height - $h;
                break;
            case GravityInterface::GRAVITY_SOUTH:
                $x = ($width / 2) - ($w / 2);
                $y = $height - $h;
                break;
            case GravityInterface::GRAVITY_SOUTHEAST:
                $x = $width - $w;
                $y = $height - $h;
                break;
        }

        return new Point((int)ceil($x), (int)ceil($y));
    }
}
