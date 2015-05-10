<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Http;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;
use Thapp\JitImage\Http\ImageResponse;
use Thapp\JitImage\Resource\ResourceInterface;
use Thapp\JitImage\Resolver\ResolverInterface;
use Thapp\JitImage\Resolver\ImageResolverInterface;
use Thapp\JitImage\Http\HttpSignerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Thapp\JitImage\Exception\InvalidSignatureException;
use Thapp\JitImage\Exception\ImageNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * urlSigner
     *
     * @var mixed
     */
    private $signer;

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
    public function setImageResolver(ImageResolverInterface $imageResolver)
    {
        $this->imageResolver  = $imageResolver;
    }

    /**
     * setUlrSigner
     *
     * @param HttpSignerInterface $signer
     *
     * @return void
     */
    public function setUrlSigner(HttpSignerInterface $signer)
    {
        $this->signer  = $signer;
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
    public function getImage($path, $params = null, $source = null, $filters = null)
    {
        list ($parameters, $filterExpr) = $this->getParamsAndFilters($params, $filters);

        return $this->resolveImage($path, $source, $parameters, $filterExpr);
    }

    /**
     * Resolve an aliased route
     *
     * @param string $route
     * @param string $alias
     * @param string $source
     *
     * @throws NotFoundHttpException if image was not found
     * @return ImageResponse
     */
    public function getResource($recipe, $source)
    {
        if (null === $this->recipes) {
            $this->notFound($source);
        }

        list($path, $params, $filter) = $this->recipes->resolve($recipe);

        return $this->resolveImage($path, $source, $params, $filter);
    }

    /**
     * Resolve a cache route
     *
     * @param string $path
     * @param string $id
     *
     * @throws NotFoundHttpException if image was not found
     * @return ImageResponse
     */
    public function getCached($path, $id)
    {
        if (!$resource = $this->imageResolver->resolveCached($path, $id)) {
            $this->notFound($id);
        }

        return $this->processResource($resource, $this->getRequest());
    }

    /**
     * resolveImage
     *
     * @param mixed $path
     * @param mixed $source
     * @param Parameters $params
     * @param FilterExpression $filter
     *
     * @throws NotFoundHttpException if image was not found
     * @return ImageResponse
     */
    protected function resolveImage($path, $source, Parameters $params, FilterExpression $filter = null)
    {
        $this->validateRequest($req = $this->getRequest(), $params, $filter);

        if (!$resource = $this->imageResolver->resolve($source, $params, $filter, $path)) {
            $this->notFound($source);
        }

        return $this->processResource($resource, $req);
    }

    /**
     * Validates current Request
     *
     * @param Request $request
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @throws BadRequestHttpException if validation fails
     * @return boolean
     */
    private function validateRequest(Request $request, Parameters $params, FilterExpression $filters = null)
    {
        if (null === $this->signer) {
            return true;
        }

        try {
            return $this->signer->validate($request, $params, $filters);
        } catch (InvalidSignatureException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return true;
    }

    /**
     * getParamsAndFilters
     *
     * @param string $params
     * @param string $filters
     *
     * @return array
     */
    protected function getParamsAndFilters($params, $filters = null)
    {
        return [Parameters::fromString($params), $filters ? new FilterExpression($filters) : null];
    }

    /**
     * Get the current Request object.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * processResource
     *
     * @param ResourceInterface $resource
     * @param Request $request
     *
     * @return ImageResponse
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
        throw new ImageNotFoundException(sprintf('Resource "%s" could not be found.', $source));
    }
}
