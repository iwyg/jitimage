<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage\Resolver package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resolver;

use \Thapp\Image\ProcessorInterface;
use \Thapp\JitImage\Resource\ImageResource;
use \Thapp\JitImage\Cache\CacheAwareInterface;
use \Thapp\JitImage\Validator\ValidatorInterface;
use \Thapp\JitImage\Response\GenericFileResponse as Response;

/**
 * @class ImageResolver implements ResolverInterface
 * @see ResolverInterface
 *
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Resolver
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class ImageResolver implements ParameterResolverInterface
{
    use ImageResolverHelper;

    /**
     * processor
     *
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * cacheResolver
     *
     * @var ResolverInterface
     */
    private $cacheResolver;

    /**
     * constraintValidator
     *
     * @var ValidatorInterface
     */
    private $constraintValidator;

    /**
     * Create a new ImageResolver instance.
     *
     * @param ProcessorInterface $processor
     */
    public function __construct(
        ProcessorInterface $processor,
        ResolverInterface $cacheResolver = null,
        ValidatorInterface $constraintValidator = null
    ) {
        $this->processor = $processor;
        $this->cacheResolver = $cacheResolver;
        $this->constraintValidator = $constraintValidator;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return ResolverInterface
     */
    public function getCacheResolver()
    {
        return $this->cacheResolver;
    }

    /**
     * Resolve the url parameters to a image resource.
     *
     * @param array $params
     *
     * @return ResourceInterface
     */
    public function resolveParameters(array $parameters)
    {
        list ($path, $params, $source, $filter, $alias) = array_pad($parameters, 4, null);

        $cache = $this->cacheResolver ? $this->cacheResolver->resolve(trim($alias, '/')) : null;
        $path = $this->getPath($path, $source);

        $key = null;

        if (null !== $cache && $cache->has($key = $this->makeCacheKey($cache, $path, $params, $filter)) &&
            $resource = $cache->get($key)
        ) {
            return $resource;
        }

        $params = array_merge($this->extractParamString($params), ['filter' => $this->extractFilterString($filter)]);
        $this->validateParams($params);

        return $this->applyProcessor($this->processor, $path, $params, $cache, $key);
    }

    /**
     * Resolve the url parameters to a cached image resource.
     *
     * @param array $params
     *
     * @return ResourceInterface
     */
    public function resolveCached(array $parameters)
    {
        $prefix = trim(substr($parameters[0], 0, strrpos($parameters[0], '/')), '/');

        if (null === ($cache = $this->cacheResolver->resolve($prefix))) {
            return;
        }

        $pos = strrpos($parameters[1], '.');

        $key = strtr($parameters[1], ['/' => '.']);
        $key = false !== $pos ? substr($key, 0, $pos) : $key;

        if (!$cache->has($key)) {
            return;
        }

        return $cache->get($key);
    }

    /**
     * Validate the url parameters against constraints.
     *
     * @param array $params
     *
     * @throws \OutOfBoundsException if validation fails
     * @return void
     */
    private function validateParams(array $params = [])
    {
        if (null === $this->constraintValidator) {
            return;
        }

        if (!$this->constraintValidator->validate($params['mode'], [$params['width'], $params['height']])) {
            throw new \OutOfBoundsException('Parameters exceed limit');
        }
    }
}
