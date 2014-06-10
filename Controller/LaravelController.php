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
use \Illuminate\Routing\Controller;
use \Thapp\Image\Resource\ResourceInterface;
use \Thapp\JitImage\Response\ImageResponse;
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Thapp\JitImage\Resolver\ParameterResolverInterface;
use \Thapp\JitImage\Controller\Traits\ImageControllerTrait;
use \Symfony\Component\HttpFoundation\Response;

/**
 * @class LaravelController extends Controller LaravelController
 * @see Controller
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class LaravelController extends Controller
{
    /**
     * router
     *
     * @var Router
     */
    private $router;

    private $request;

    private $path;

    private $pathResolver;

    private $imageResolver;

    /**
     * @param ResolverInterface $pathResolver
     */
    public function __construct(ResolverInterface $pathResolver, ParameterResolverInterface $imageResolver)
    {
        $this->pathResolver  = $pathResolver;
        $this->imageResolver = $imageResolver;
    }

    /**
     * setRouter
     *
     * @param mixed $router
     *
     * @access public
     * @return void
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * setRequest
     *
     * @param mixed $request
     *
     * @access public
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * getImage
     *
     * @param string $alias
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return Response
     */
    public function getImage($alias, $params = null, $source = null, $filter = null)
    {
        $resource = $this->imageResolver->resolveParameters(
            [
                $this->pathResolver->resolve($alias),
                $params,
                $source,
                $filter,
                $alias
            ]
        );

        return $this->processResource($resource);
    }

    /**
     * getCached
     *
     * @param string $path
     * @param string $id
     *
     * @return Response
     */
    public function getCached($path, $id)
    {
        if (!$resource = $this->imageResolver->resolveCached([$path, $id])) {
            return $this->notFound();
        }

        return $this->processResource($resource);
    }

    /**
     * processResource
     *
     * @param mixed $resource
     *
     * @access private
     * @return Response
     */
    private function processResource(ResourceInterface $resource)
    {
        $response = new ImageResponse($resource);

        $response->prepare($this->request);
        $response->send();

        return $response;
    }

    /**
     * notFournd
     *
     * @access private
     * @return void
     */
    private function notFournd()
    {
        return new Response('Resource not found', 404);
    }

    /**
     * callAction
     *
     * @param mixed $method
     * @param mixed $parameters
     *
     * @access public
     * @return Response
     */
    public function callAction($method, $parameters)
    {
        array_unshift($parameters, $this->getCurrentPath());

        return call_user_func_array([$this, $method], $parameters);
    }

    /**
     * getCurrentPath
     *
     * @access private
     * @return string
     */
    private function getCurrentPath()
    {
        return $this->router->getCurrentRoute()->getCompiled()->getStaticPrefix();
    }
}
