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
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var Router
     */
    private $router;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $path;

    /**
     * @var ResolverInterface
     */
    private $pathResolver;

    /**
     * @var ParameterResolverInterface
     */
    private $imageResolver;

    /**
     * @var ResolverInterface
     */
    private $recipes;

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
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param ResolverInterface $recipes
     *
     * @return void
     */
    public function setRecieps(ResolverInterface $recipes)
    {
        $this->recipes = $recipes;
    }

    /**
     * Resolve an aliased route
     *
     * @param string $route
     * @param string $alias
     * @param string $source
     *
     * @throws NotFoundHttpException if image was not found
     * @return Response
     */
    public function getResource($route, $alias, $source)
    {
        if (null === $this->recipes) {
            $this->notFound($source);
        }

        list($params, $filter) = $this->recipes->resolve($alias);

        return $this->getImage($route, $params, $source, $filter);
    }

    /**
     * Resolve a dynamic route
     *
     * @param string $alias
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @throws NotFoundHttpException if image was not found
     * @return Response
     */
    public function getImage($alias, $params = null, $source = null, $filter = null)
    {
        if (!$resource = $this->imageResolver->resolveParameters(
            [
                $this->pathResolver->resolve($alias),
                $params,
                $source,
                $filter,
                $alias
            ]
        )
        ) {
            $this->notFound($source);
        }

        return $this->processResource($resource);
    }

    /**
     * Resolve a cache route
     *
     * @param string $path
     * @param string $id
     *
     * @throws NotFoundHttpException if image was not found
     * @return Response
     */
    public function getCached($path, $id)
    {
        if (!$resource = $this->imageResolver->resolveCached([$path, $id])) {
            $this->notFound($id);
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
     * @throws NotFoundHttpException always
     *
     * @return void
     */
    private function notFound($source)
    {
        throw new NotFoundHttpException(sprintf('resource "%s" not found', $source));
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
