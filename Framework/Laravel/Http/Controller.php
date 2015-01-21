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
            $parameters = ['prefix' => $this->getCurrentPath()]+$parameters;
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
