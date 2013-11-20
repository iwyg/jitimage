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

use \Illuminate\Http\Response;
use \Illuminate\Routing\Router;
use \Thapp\JitImage\ImageInterface;
use \Illuminate\Container\Container;
use \Thapp\JitImage\ResolverInterface;
use \Illuminate\Routing\Controllers\Controller;
use \Thapp\JitImage\Response\FileResponseInterface;

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
            $this->render($image);
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
    public function getResource($source)
    {
        extract($this->defaults);

        if (is_string($parameter) and strlen($parameter) > 0) {

            list($parameters, $filter) = array_pad(explode(',', str_replace(' ', null, $parameter)), 2, null);

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
     * Set the bound defaul values and execute the parent callAction() method.
     *
     * @see \Illuminate\Routing\Controller\Controller#callAction()
     * @access public
     * @return void
     */
    public function callAction(Container $container, Router $router, $method, $parameters)
    {
        $this->defaults = $router->getCurrentRoute()->getDefaults();
        parent::callAction($container, $router, $method, $parameters);
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
        $this->response->send();
    }
}
