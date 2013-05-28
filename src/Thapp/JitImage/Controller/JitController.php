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

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Routing\Controller\Controller;
use Thapp\JitImage\ImageInterface;
use Illuminate\Container\Container;
use Thapp\JitImage\ResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class: JitController
 *
 * @uses Controller
 *
 * @package Thapp\JitImage
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
     * Create a new JitController
     *
     * @param  ResolverInterface $imageResolver The resolver object
     *
     * @access public
     */
    public function __construct(ResolverInterface $imageResolver)
    {
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

            list($parameters, $filter) = array_pad(
                explode(',', str_replace(' ', null, $parameter)),
                2, null
            );

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
        throw new NotFoundHttpException;
    }


    /**
     * Create a new response and send its contents.
     *
     * @access protected
     * @return void
     */
    protected function render(ImageInterface $image)
    {
        $response = new Response($image->getContents(), 200);
        $response->header('Content-type', $image->getMimeType());

        $image->close();
        $this->imageResolver->close();

        $response->send();
    }
}
