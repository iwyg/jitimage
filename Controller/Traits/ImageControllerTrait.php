<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Controller\Traits;

use \Thapp\Image\Resource\ResourceInterface;
use \Thapp\JitImage\Response\ImageResponse;
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Thapp\JitImage\Resolver\ParameterResolverInterface;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @class ImageControllerTrait
 * @package Thapp\JitImage
 * @version $Id$
 */
trait ImageControllerTrait
{

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
     * pathResolver
     *
     * @param ResolverInterface $pathResolver
     *
     * @return void
     */
    public function setPathResolver(ResolverInterface $pathResolver)
    {
        $this->pathResolver  = $pathResolver;
    }

    /**
     * pathResolver
     *
     * @param ParameterResolverInterface $imageResolver
     *
     * @return void
     */
    public function setImageResolver(ParameterResolverInterface $imageResolver)
    {
        $this->imageResolver  = $imageResolver;
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
    public function getImage($path, $params = null, $source = null, $filter = null)
    {
        if (!$resource = $this->imageResolver->resolveParameters(
            [
                $this->pathResolver->resolve($path),
                $params,
                $source,
                $filter,
                $path
            ]
        )
        ) {
            $this->notFound($source);
        }

        return $this->processResource($resource);
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
}
