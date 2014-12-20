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

    public function setRecipeResolver(RecipeResolverInterface $recipes)
    {
        $this->recipeResolver = $recipes;
    }

    public function getImage(Request $request, $path, $params, $source, $filter = null)
    {
        if (!$resource = $this->imageResolver->resolveParameters([$path, $params, $source, $filter])) {
            $this->notFound($source);
        }

        return $this->processResource($resource, $request);
    }

    public function getResource(Request $request, $recipe, $source)
    {
        list ($alias, $params, $filter) = $this->recipeResolver->resolve($recipe);

        return $this->getImage($request, $alias, $params, $source, $filter);
    }

    public function getCached(Request $request, $prefix, $id)
    {
        var_dump('get cached');
        die;
    }

    /**
     * processResource
     *
     * @param mixed $resource
     *
     * @access private
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
