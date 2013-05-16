<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\controller package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Controller;

use Thapp\JitImage\ImageInterface;

/**
 * @class JitController
 */
class JitController extends \BaseController
{
    /**
     * image
     *
     * @var \Thapp\JitImage\ImageInterface
     */
    protected $image;

    protected $processCache;

    /**
     * __construct
     *
     * @param Image $image
     * @access public
     */
    public function __construct(ImageInterface $image, CacheInterface $cache)
    {
        $this->image        = $image;
        $this->processCache = $cache;
    }

    /**
     * getIndex
     *
     * @access public
     * @return mixed
     */
    public function getIndex()
    {

    }

    /**
     * getImage
     *
     * @param mixed $mode
     * @param mixed $height
     * @param mixed $width
     * @param mixed $gravity
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function getImage($mode, $height, $width, $gravity, $source)
    {

    }

    /**
     * process
     *
     * @access protected
     * @return mixed
     */
    protected function process()
    {

    }

    /**
     * render
     *
     * @access protected
     * @return mixed
     */
    protected function render()
    {

    }
}
