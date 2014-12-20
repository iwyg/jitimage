<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\View;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;
use Thapp\JitImage\Cache\CacheInterface;
use Thapp\JitImage\Resolver\ImageResolverHelper;
use Thapp\JitImage\Resolver\ImageResolverInterface;
use Thapp\JitImage\Resolver\RecipeResolverInterface;

/**
 * @class Jmg
 *
 * @package Thapp\JitImage\Template
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Jmg
{
    use ImageResolverHelper;

    private $pool;
    private $recipes;
    private $defaultPath;
    private $cachePrefix;

    /**
     * Constructor.
     *
     * @param ImageResolverInterface $imageResolver
     * @param RecipeResolverInterface $recipes
     */
    public function __construct(ImageResolverInterface $imageResolver, RecipeResolverInterface $recipes, $default = '', $cPrefix = 'cached')
    {
        $this->imageResolver = $imageResolver;
        $this->recipes = $recipes;
        $this->defaultPath = $default;
        $this->cacheSuffix = $cPrefix;

        $this->pool = [];
    }

    /**
     * take
     *
     * @param string $source
     * @param string $path
     *
     * @return Generator
     */
    public function take($source, $path = null)
    {
        $path = $path ?: $this->defaultPath;
        $gen = $this->newGenerator();

        $gen->setPath($path);
        $gen->setSource($source);

        return $gen;
    }

    /**
     * Creates an image path from a given recipe
     *
     * @param string $recipe
     *
     * @return string
     */
    public function make($recipe, $source)
    {
        if (!$res = $this->recipes->resolve($recipe)) {
            return '';
        }

        list ($prefix, $params, $filter) = $res;

        return $this->apply(
            $prefix,
            $source,
            Parameters::fromString($params),
            $filter ? $this->filter($filter) : null
        );
    }

    /**
     * filter
     *
     * @param string $expr
     *
     * @return Jmg
     */
    protected function filter($expr)
    {
        return new FilterExpression($expr);
    }

    /**
     * close
     *
     * @return void
     */
    protected function close()
    {
        $this->imageResolver->getProcessor()->close();
    }

    /**
     * apply
     *
     *
     * @return void
     */
    protected function apply($path, $source, Parameters $parameters, FilterExpression $filters = null)
    {
        list ($params, $filter) = $parts = $this->listParamsAndFilter($parameters, $filters);
        $pmStr = $this->getParamString($params, $source, $filter);

        $cr = $this->imageResolver->getCacheResolver();

        if (null !== $cr = $this->imageResolver->getCacheResolver() && ($cache = $cr->resolve($path))) {
            return $this->resolveFromCache($cache, $path, $source, $parts, $parameters, $filters);
        }

        return $this->getUri($path, $pmStr);
    }

    /**
     * resolveFromCache
     *
     * @param mixed $cache
     * @param mixed $fragments
     *
     * @access protected
     * @return mixed
     */
    protected function resolveFromCache(CacheInterface $cache, $path, $source, array $parts, Parameters $params, FilterExpression $filter = null)
    {
        list ($pStr, $fStr) = $parts;
        $src = $this->getPath($this->imageResolver->getPathResolver()->resolve($path), $source);

        if (!$cache->has($key = $this->createCacheKey($cache, $src, $pStr, $fStr))) {
            $this->process($cache, $params, $src, $key, $path, $filter);
        }

        // If the image is not cached yet, this is the only time the processor
        // is invoked:
        if (!isset($this->pool[$key])) {

            $cached = $cache->get($key);

            $file = $cached->getFileName();
            $dir  = basename(dirname($cached->getPath()));
            $str = '/'. implode('/', [$path, $this->cacheSuffix, $dir, $file]);

            $this->pool[$key] = $str;
        }

        return $this->pool[$key];
    }

    /**
     * process
     *
     * @param CacheInterface $cache
     * @param string $src
     * @param string $key
     *
     * @return void
     */
    protected function process(CacheInterface $cache, Parameters $params, $src, $key, $path, FilterExpression $filters = null)
    {
        $proc = $this->imageResolver->getProcessor();

        if (null === $loader = $this->imageResolver->getLoaderResolver()->resolve($path)) {
            throw new \InvalidArgumentException;
        }

        if (!$loader->supports($src)) {
            throw new \InvalidArgumentException;
        }

        $proc->load($loader->load($src));
        $proc->process($this->compileExpression($params, $filters));

        $cache->set($key, $proc);

        $this->close();
    }

    /**
     * compileExpression
     *
     * @return array
     */
    protected function compileExpression(Parameters $parameters, FilterExpression $filters = null)
    {
        return array_merge($parameters->all(), ['filter' => $filters ? $filters->toArray() : []]);
    }

    /**
     * getUri
     *
     * @param array $fragments
     *
     * @return string
     */
    private function getUri($path, $fragments)
    {
        return '/' . implode('/', [trim($path, '/'), $fragments]);
    }

    /**
     * getParamString
     *
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return string
     */
    private function getParamString($params, $source, $filter = null)
    {
        return null !== $filter ? implode('/', [$params, $source, $filter]) : implode('/', [$params, $source]);
    }

    /**
     * compileExpression
     *
     * @access protected
     * @return array
     */
    protected function listParamsAndFilter(Parameters $params, FilterExpression $filter = null)
    {
        return [
            $params->asString(),
            !empty($filter ? $filter->toArray() : []) ? sprintf('filter:%s', $filter->compile()) : null
        ];
    }

    /**
     * createCacheKey
     *
     * @param CacheInterface $cache
     * @param string $path
     * @param string $paramStr
     * @param string $filterStr
     *
     * @return string
     */
    private function createCacheKey(CacheInterface $cache, $path, $paramStr, $filterStr)
    {
        $p = $path.$paramStr.$filterStr;

        if (isset($this->pool[$p])) {
            return $this->pool[$p];
        }

        return $this->pool[$p] = $this->makeCacheKey($cache, $path, $paramStr, $filterStr);
    }

    /**
     * newGenerator
     *
     * @return Generator
     */
    protected function newGenerator()
    {
        if (null === $this->generator) {
            return $this->generator = new Generator($this);
        }

        return clone $this->generator;
    }

}
