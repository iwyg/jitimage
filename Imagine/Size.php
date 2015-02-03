<?php

/*
 * This File is part of the Thapp\JitImage\Imagine package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Imagine;

use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Thapp\Image\Geometry\Size as BaseSize;

/**
 * @class Size
 *
 * @package Thapp\JitImage\Imagine
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Size
{
    private $size;

    public function __construct($width, $height)
    {
        $this->size = new BaseSize($width, $height);
    }

    public function fill(BoxInterface $box)
    {
        $size = $this->size->fill(new BaseSize($box->getWidth(), $box->getHeight()));

        return new Box($size->getWidth(), $size->getHeight());
    }

    public function fit(BoxInterface $box)
    {
        $size = $this->size->fit(new BaseSize($box->getWidth(), $box->getHeight()));

        return new Box($size->getWidth(), $size->getHeight());
    }

    public function pixel($pix)
    {
        $size = $this->size->pixel($pix);

        return new Box($size->getWidth(), $size->getHeight());
    }
}
