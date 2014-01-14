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

use \Illuminate\Routing\Router;
use \Thapp\JitImage\ImageInterface;
use \Illuminate\Routing\Controller;
use \Illuminate\Container\Container;
use \Thapp\JitImage\ResolverInterface;
use \Thapp\JitImage\Response\FileResponseInterface;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class JitController extends Controller JitController
 * @see Controller
 *
 * @package Thapp\JitImage\Controller
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitController extends Controller
{
    /**
     * image
     *
     * @var \Thapp\JitImage\ImageInterface
     */
    protected $image;

    /**
     * defaults
     *
     * @var array
     */
    protected $defaults;

    /**
     * defaults
     *
     * @var mixed
     */
    protected $response;

    /**
     * Create a new JitController
     *
     * @param  ResolverInterface $imageResolver The resolver object
     *
     * @access public
     */
    public function __construct(ResolverInterface $imageResolver, FileResponseInterface $response)
    {
        $this->response      = $response;
        $this->imageResolver = $imageResolver;
    }

    /**
     * Handler method that's being called for dynamic image processing.
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

        if ($image = $this->imageResolver->resolve()) {
            return $this->render($image);
        }

        return $this->notFound();
    }

    /**
     * Handler method that's being called for aliased image processing.
     *
     * @param  string $source the source image to be processed
     *
     * @access public
     * @return mixed
     */
    public function getResource($parameter, $source)
    {
        if ($params = $this->recipes->resolve($parameter)) {
            extract($params);
            return $this->getImage($parameters, $source, $filter);
        }

        return $this->notFound();
    }

    /**
     * Handler method for resolving cached images.
     *
     * @param mixed $key
     * @access public
     * @return void
     */
    public function getCached($key)
    {
        if ($image = $this->imageResolver->resolveFromCache($key)) {
            return $this->render($image);
        }

        return $this->notFound();
    }

    /**
     * setRecieps
     *
     * @param \Thapp\JitImage\RecipeResolver $recipes
     *
     * @access public
     * @return void
     */
    public function setRecieps(\Thapp\JitImage\RecipeResolver $recipes)
    {
        $this->recipes = $recipes;
    }

    /**
     * Hanlde image not found error.
     *
     * @throws NotFoundHttpException
     * @access protected
     * @return void
     */
    protected function notFound()
    {
        $this->imageResolver->close();
        $this->response->notFound();
    }


    /**
     * Create a new response and send its contents.
     *
     * @access protected
     * @return void
     */
    protected function render(ImageInterface $image)
    {
        $this->response->make($image);
        $image->close();

        return $this->response->getResponse();
    }
}
