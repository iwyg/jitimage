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
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Thapp\JitImage\Resolver\ParameterResolverInterface;
use \Thapp\JitImage\Controller\Traits\ImageControllerTrait;

/**
 * @class LaravelController extends Controller implements ImageControllerInterface
 * @see Controller
 * @see ImageControllerInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class LaravelController extends Controller implements ImageControllerInterface
{

    use ImageControllerTrait;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param ResolverInterface $pathResolver
     * @param ParameterResolverInterface $imageResolver
     */
    public function __construct(ResolverInterface $pathResolver, ParameterResolverInterface $imageResolver)
    {
        $this->setPathResolver($pathResolver);
        $this->setImageResolver($imageResolver);
    }

    public function getCachedResource($route, $suffix, $id, $path = 'test')
    {
        $path = trim($route, '/');

        if (!$resource = $this->imageResolver->resolveCached([$path, $id])) {
            $this->notFound($id);
        }

        return $this->processResource($resource);
    }

    /**
     * setRouter
     *
     * @param mixed $router
     *
     * @return void
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
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
        $r = new \ReflectionObject($this->router->getCurrentRoute());
        return $this->router->getCurrentRoute()->getCompiled()->getStaticPrefix();
    }
}
