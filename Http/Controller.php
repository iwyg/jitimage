<?php

/*
 * This File is part of the Thapp\JitImage\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Http;

use Thapp\JitImage\Http\ImageResponse;
use Thapp\JitImage\Resource\ResourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Thapp\JitImage\Resolver\ImageResolverInterface;
use Thapp\JitImage\Resolver\RecipeResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @class Controller
 *
 * @package Thapp\JitImage\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Controller
{
    protected $imageResolver;
    protected $recipeResolver;

    public function setImageResolver(ImageResolverInterface $imageResolver)
    {
        $this->imageResolver = $imageResolver;
    }

    /**
     * setRecipeResolver
     *
     * @param RecipeResolverInterface $recipes
     *
     * @return void
     */
    public function setRecipeResolver(RecipeResolverInterface $recipes)
    {
        $this->recipeResolver = $recipes;
    }

    /**
     * getImage
     *
     * @param Request $request
     * @param string $path
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return Response
     */
    public function getImage(Request $request, $path, $params, $source, $filter = null)
    {
        if (!$resource = $this->imageResolver->resolveParameters([$path, $params, $source, $filter])) {
            $this->notFound($source);
        }

        return $this->processResource($resource, $request);
    }

    /**
     * getResource
     *
     * @param Request $request
     * @param string $recipe
     * @param string $source
     *
     * @return Response
     */
    public function getResource(Request $request, $recipe, $source)
    {
        if (null === $this->recipes) {
            $this->notFound($source);
        }

        list ($alias, $params, $filter) = $this->recipeResolver->resolve($recipe);

        return $this->getImage($request, $alias, $params, $source, $filter);
    }

    /**
     * getCached
     *
     * @param Request $request
     * @param string $prefix
     * @param string $id
     *
     * @return Response
     */
    public function getCached(Request $request, $suffix, $id)
    {
        if (!$resource = $this->imageResolver->resolveCached([$suffix, $id])) {
            $this->notFound($id);
        }

        return $this->processResource($resource, $request);
    }

    /**
     * processResource
     *
     * @param mixed $resource
     *
     * @return Response
     */
    private function processResource(ResourceInterface $resource, Request $request)
    {
        $response = new ImageResponse($resource);

        $response->prepare($request);
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
