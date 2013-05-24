<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Controller;

use Thapp\JitImage\ResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class: JitController
 *
 * @uses \BaseController
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitController extends \BaseController
{
    /**
     * image
     *
     * @var \Thapp\JitImage\ImageInterface
     */
    protected $image;

    /**
     * __construct
     *
     * @param Image $image
     * @access public
     */
    public function __construct(ResolverInterface $imageResolver)
    {
        $this->imageResolver = $imageResolver;
    }

    /**
     * getImage
     *
     * @param string $mode
     * @param string $height
     * @param string $width
     * @param string $gravity
     * @param string $options
     * @access public
     *
     * @return void
     */
    public function getImage($parameter, $source, $filter = null)
    {
        $this->imageResolver->setParameter($parameter);
        $this->imageResolver->setSource($source);
        $this->imageResolver->setFilter($filter);

        if (!$image = $this->imageResolver->resolve()) {
            throw new NotFoundHttpException;
        }

        return $this->render($image);
    }

    /**
     * render
     *
     * @access protected
     * @return mixed
     */
    protected function render($src)
    {
        header('Content-type: ' .  $this->imageResolver->getImage()->getFileFormat());
        echo $src;
        exit(0);
    }
}
