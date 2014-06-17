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
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class JitImageExtension extends \Twig_Extension
{
    /**
     * image
     *
     * @var JitImage
     */
    private $image;

    /**
     * from
     *
     * @var string
     */
    private $from;

    /**
     * Creates a new Twig Extension
     *
     * @param JitImage $image
     */
    public function __construct(JitImage $image)
    {
        $this->image = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'jitimage';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('jmg', function ($location = null, $ext = false) {
                return $this->image->from($location, $ext);
            })
        ];
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
            new \Twig_SimpleFilter('jmg_make', function ($src, $recipe, $addExtension = false) {
                $img = $this->getImage($src, null, $addExtension);

                return $img->make($recipe);
            }),
            new \Twig_SimpleFilter('jmg_get', [$this, 'filterGet']),
            new \Twig_SimpleFilter('jmg_resize', [$this, 'filterResize']),
            new \Twig_SimpleFilter('jmg_crop', [$this, 'filterCrop']),
            new \Twig_SimpleFilter('jmg_crop_resize', [$this, 'filterCropResize']),
            new \Twig_SimpleFilter('jmg_fit', [$this, 'filterFit']),
            new \Twig_SimpleFilter('jmg_scale', [$this, 'filterScale']),
            new \Twig_SimpleFilter('jmg_pixel', [$this, 'filterPixel']),
        ];
    }

    /**
     * filterGet
     *
     * @param string $src  image source
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterGet($src, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->get();
    }

    /**
     * filterResize
     *
     * @param string $src  image source
     * @param int    $w    image width
     * @param int    $h    image height
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterResize($src, $w = 0, $h = 0, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->resize($w, $h);
    }

    /**
     * filterResize
     *
     * @param string $src  image source
     * @param int    $w    image width
     * @param int    $h    image height
     * @param int    $g    image gravity
     * @param string $c    image background color
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterCrop($src, $w = null, $h = null, $g = 5, $c = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->resize($w, $h, $g, $c);
    }

    /**
     * filterCropResize
     *
     * @param string $src  image source
     * @param int    $w    image width
     * @param int    $h    image height
     * @param int    $g    image gravity
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterCropResize($src, $w = null, $h = null, $g = 5, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->cropAndResize($w, $h, $g);
    }

    /**
     * filterFit
     *
     * @param string $src  image source
     * @param int    $w    image width
     * @param int    $h    image height
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterFit($src, $w = null, $h = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->fit($w, $h);
    }

    /**
     * filterScale
     *
     * @param string $src  image source
     * @param int    $p    scaling value in percent
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterScale($src, $p = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->scale($p);
    }

    /**
     * filterPixel
     *
     * @param string $src  image source
     * @param int    $px   image pixel
     * @param string $f    image filter
     * @param bool   $e    print file extension
     *
     * @return string
     */
    public function filterPixel($src, $px = null, $f = null, $e = false)
    {
        $image = $this->getImage($src, $f, $e);

        return $image->pixel($px);
    }

    /**
     * Get the JitImage instance
     *
     * @param string $src
     * @param string $filters
     * @param bool   $addExtension
     *
     * @return JitImage
     */
    private function getImage($src, $filters = null, $addExtension = false)
    {
        $img = $this->image->from($this->getLocation());

        if ($addExtension) {
            $img->withExtension();
        }

        $img->load($src);

        if (null !== $filters) {
            $img->filter($filters);
        }

        return $img;
    }

    /**
     * Get the current location
     *
     * @return string|null
     */
    private function getLocation()
    {
        $location = $this->from;
        $this->from = null;

        return $location;
    }
}
