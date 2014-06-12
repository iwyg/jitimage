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

use \Symfony\Component\HttpFoundation\Request;
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Thapp\JitImage\Resolver\ParameterResolverInterface;
use \Thapp\JitImage\Controller\Traits\ImageControllerTrait;

/**
 * @class SilexController implements ImageControllerInterface
 * @see ImageControllerInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class SilexController implements ImageControllerInterface
{
    use ImageControllerTrait;

    /**
     * @param ResolverInterface $pathResolver
     * @param ParameterResolverInterface $imageResolver
     */
    public function __construct(ResolverInterface $pathResolver, ParameterResolverInterface $imageResolver)
    {
        $this->setPathResolver($pathResolver);
        $this->setImageResolver($imageResolver);
    }

    public function getImageAction($params, $source, $filter)
    {
        return $this->getImage($this->getCurrentPath(), $params, $source, $filter);
    }

    protected function getCurrentPath()
    {
        return $this->currentPath;
    }

    public function setCurrentPath($path)
    {
        $this->currentPath = $path;
    }
}
