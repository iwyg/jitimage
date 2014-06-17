<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Twig;

use \Thapp\JitImage\JitImage;

/**
 * @class JitImageExtension
 * @package Thapp\JitImage
 * @version $Id$
 */
class JitImageExtension extends \Twig_Extension
{
    private $image;

    private $from;

    public function __construct(JitImage $image)
    {
        $this->image = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'jitimage_filter';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('jmg_from', function ($src, $location) {
                $this->from = $location;
                return $src;

            }),
            new \Twig_SimpleFilter('jmg_scale', [$this, 'filterScale']),
            new \Twig_SimpleFilter('jmg_fit', [$this, 'filterFit']),
            new \Twig_SimpleFilter('jmg_crop_resize', [$this, 'filterCropResize']),
            new \Twig_SimpleFilter('jmg_resize', [$this, 'filterResize']),
            new \Twig_SimpleFilter('jmg_pixel', [$this, 'filterPixel']),
            new \Twig_SimpleFilter('jmg_get', [$this, 'filterGet']),
        ];
    }

    public function filterScale($src, $p = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->scale($p);
    }

    public function filterPixel($src, $px = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->pixel($px);
    }

    public function filterGet($src, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->get();
    }

    public function filterFit($src, $w = null, $h = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->fit($w, $h);
    }

    public function filterResize($src, $w = null, $h = null, $g = 5, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->resize($w, $h, $g);
    }

    public function filterCropResize($src, $w = null, $h = null, $g = 5, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->cropAndResize($w, $h, $g);
    }

    private function getImage($src, $filters = null, $addExtension = false)
    {
        $img = $this->image->from($this->getLocation() ?: 'image', $addExtension);

        $img->load($src);

        if (null !== $filters) {
            $img->filterExpression($filters);
        }

        return $img;
    }

    private function getLocation()
    {
        $location = $this->from;
        $this->from = null;

        return $location;
    }
}
