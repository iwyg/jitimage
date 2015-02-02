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

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;
use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Resource\ImageResource;
use Thapp\JitImage\Cache\CacheAwareInterface;
use Thapp\JitImage\Validator\ValidatorInterface;
use Thapp\JitImage\Response\GenericFileResponse as Response;
use Thapp\JitImage\Http\HttpSingerInterface;

/**
 * @class ImageResolver implements ResolverInterface
 * @see ResolverInterface
 *
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Resolver
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class ImageResolver implements ImageResolverInterface
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
     * pathResolver
     *
     * @var mixed
     */
    private $pathResolver;

    /**
     * loaderResolver
     *
     * @var mixed
     */
    private $loaderResolver;

    /**
     * constraintValidator
     *
     * @var ValidatorInterface
     */
    private $constraintValidator;

    /**
     * urlSigner
     *
     * @var mixed
     */
    private $urlSigner;

    private $pool;

    /**
     * Create a new ImageResolver instance.
     *
     * @param ProcessorInterface $processor
     */
    public function __construct(
        ProcessorInterface $processor,
        PathResolverInterface $pathResolver,
        LoaderResolverInterface $loaderResolver,
        CacheResolverInterface $cacheResolver = null,
        ValidatorInterface $constraintValidator = null
    ) {
        $this->processor = $processor;
        $this->pathResolver = $pathResolver;
        $this->loaderResolver = $loaderResolver;
        $this->cacheResolver = $cacheResolver;
        $this->constraintValidator = $constraintValidator;
        $this->pool = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheResolver()
    {
        return $this->cacheResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoaderResolver()
    {
        return $this->loaderResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathResolver()
    {
        return $this->pathResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($src, Parameters $params, FilterExpression $filters = null, $prefix = '')
    {
        if (!isset($this->pool[$pk = $this->poolKey($prefix, $src, $params, $filters)])) {
            $this->pool[$pk] = $this->resolveImage($src, $params, $filters, $prefix);
        }

        return $this->pool[$pk];
    }

    /**
     * Resolve the url parameters to a cached image resource.
     *
     * @param array $params
     *
     * @return ResourceInterface
     */
    public function resolveCached($prefix, $id)
    {
        if (null === $this->cacheResolver) {
            return false;
        }

        if (null === ($cache = $this->cacheResolver->resolve($prefix = trim($prefix, '/')))) {
            return false;
        }

        $pos = strrpos($id, '.');
        $key = strtr($id, ['/' => '.']);

        $key = false !== $pos ? substr($key, 0, $pos) : $key;

        if (!$cache->has($key)) {
            return false;
        }

        return $cache->get($key);
    }

    /**
     * resolveImage
     *
     * @param mixed $src
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $prefix
     *
     * @return ResourceInterface
     */
    protected function resolveImage($src, Parameters $params, FilterExpression $filters = null, $prefix = '')
    {
        $alias = trim($prefix, '/');

        if (!$loader = $this->loaderResolver->resolve($alias)) {
            return false;
        }

        if (null === $path = $this->pathResolver->resolve($alias)) {
            return false;
        }

        $key = null;
        $cache = $this->cacheResolver ? $this->cacheResolver->resolve($alias) : null;
        $filterStr = $filters ? (string)$filters : null;

        if (null !== $cache && $cache->has($key = $this->makeCacheKey($cache, $alias, $src, (string)$params, $filterStr)) &&
            $resource = $cache->get($key)
        ) {
            return $resource;
        }

        $this->validateParams($params);

        return $this->applyProcessor($this->processor, $loader, $this->getPath($path, $src), $params, $filters, $cache, $key);
    }

    /**
     * Validate the url parameters against constraints.
     *
     * @param array $params
     *
     * @throws \OutOfBoundsException if validation fails
     * @return void
     */
    private function validateParams(Parameters $parameters)
    {
        if (null === $this->constraintValidator) {
            return;
        }

        $params = $parameters->all();

        if (false !== $this->constraintValidator->validate($params['mode'], [$params['width'], $params['height']])) {
            return true;
        }

        throw new \OutOfBoundsException('Parameters exceed limit');
    }

    /**
     * {@inheritdoc}
     */
    private function poolKey($name, $source, Parameters $params, FilterExpression $filters = null)
    {
        return sprintf('%s/%s:%s%s', $name, $source, (string)$params, $filters ? '/filter:'.($filters) : '');
    }
}
