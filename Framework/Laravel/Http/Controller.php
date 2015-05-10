<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\Controller as BaseController;
use Thapp\JitImage\Http\ImageControllerTrait;
use Thapp\JitImage\Resolver\ResolverInterface;
use Thapp\JitImage\Resolver\ImageResolverInterface;

/**
 * @class Controller
 *
 * @package Thapp\JitImage\Framework\Laravel
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Controller extends BaseController
{
    use ImageControllerTrait;

    /**
     * @param ResolverInterface $pathResolver
     * @param ParameterResolverInterface $imageResolver
     */
    public function __construct(ImageResolverInterface $imageResolver)
    {
        $this->setImageResolver($imageResolver);
    }

    /**
     * getImageResponse
     *
     * @param Request $request
     * @param mixed $prefix
     * @param mixed $params
     * @param mixed $src
     * @param mixed $filters
     *
     * @return Thapp\JitImage\Http\ImageResponse
     */
    public function getImageResponse(Request $request, $prefix, $params, $src, $filters = null)
    {
        $this->setRequest($request);

        return $this->getImage($prefix, $params, $src, $filters);
    }

    /**
     * getResourceResponse
     *
     * @param Request $request
     * @param mixed $recipe
     * @param mixed $source
     *
     * @return Thapp\JitImage\Http\ImageResponse
     */
    public function getResourceResponse(Request $request, $recipe, $source)
    {
        $this->setRequest($request);

        return $this->getResource($recipe, $source);
    }

    /**
     * getCachedResponse
     *
     * @param Request $request
     * @param mixed $path
     * @param mixed $id
     *
     * @return Thapp\JitImage\Http\ImageResponse
     */
    public function getCachedResponse(Request $request, $path, $id)
    {
        $this->setRequest($request);

        return $this->getCached($path, $id);
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
        if (!isset($parameters['path'])) {
            $request = array_shift($parameters);
            $parameters = ['path' => $this->getCurrentPath()]+$parameters;
            array_unshift($parameters, $request);
        }

        return parent::callAction($method, $parameters);
    }

    /**
     * getCurrentPath
     *
     * @access private
     * @return string
     */
    private function getCurrentPath()
    {
        return trim(static::$router->getCurrentRoute()->getCompiled()->getStaticPrefix(), '/');
    }
}
