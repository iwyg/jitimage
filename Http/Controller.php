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
    use ImageControllerTrait;

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
    public function getImageAction(Request $request, $path, $params, $source, $filter = null)
    {
        $this->request = $request;

        $this->getImage($path, $params, $source, $filter);
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
        $this->request = $request;

        $this->getResource($recipe, $source);
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
    public function getCachedAction(Request $request, $path, $id)
    {
        $this->request = $request;

        $this->getCached($path, $id);
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
