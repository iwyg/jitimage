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

use Imagine\Image\Point;
use Imagine\Image\BoxInterface;
use Thapp\Image\Geometry\Size;
use Thapp\Image\Geometry\Gravity as BaseGravity;

/**
 * @class Gravity
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Gravity implements GravityInterface
{
    private $gravity;

    /**
     * Constructor
     *
     * @param int $mode
     */
    public function __construct($mode)
    {
        $this->gravity = new BaseGravity($mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getMode()
    {
        return $this->gravity->getMode();
    }

    /**
     * {@inheritdoc}
     */
    public function getPoint(BoxInterface $source, BoxInterface $target)
    {
        $point = $this->gravity->getPoint(
            new Size($source->getWidth(), $source->getHeight()),
            new Size($target->getWidth(), $target->getHeight())
        );

        return new Point(abs($point->getX()), abs($point->getY()));
    }
}
