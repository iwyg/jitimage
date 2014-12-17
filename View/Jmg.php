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

    private $path;
    private $pool;
    private $recipes;
    private $defaultPath;

    protected $source;
    protected $filters;
    protected $parameters;

    /**
     * Constructor.
     *
     * @param ImageResolverInterface $imageResolver
     * @param RecipeResolverInterface $recipes
     */
    public function __construct(ImageResolverInterface $imageResolver, RecipeResolverInterface $recipes, $default = '')
    {
        $this->imageResolver = $imageResolver;
        $this->recipes = $recipes;

        $this->filters = new FilterExpression([]);
        $this->parameters = new Parameters;
        $this->cacheSuffix = 'cached';
    }

    /**
     * get The source image
     *
     * @param mixed $path
     *
     * @return void
     */
    public function from($path)
    {
        $this->close();

        $this->source = $path;

        return $this;
    }

    /**
     * Creates an image path from a given recipe
     *
     * @param string $recipe
     *
     * @return string
     */
    public function make($recipe)
    {
        if (!$res = $this->recipes->resolve($recipe)) {
            return;
        }

        list ($prefix, $params, $filter) = $res;

        $this->path = $prefix;

        if (null !== $filter) {
            $this->filter($filter);
        }

        $this->parameters->setFromString($params);

        return $this->apply();
    }

    /**
     * filter
     *
     * @param string $expr
     *
     * @return Jmg
     */
    public function filter($expr)
    {
        $this->filters = clone($this->filters);
        $this->filters->setExpression($expr);

        return $this;
   }

    /**
     * close
     *
     * @return void
     */
    protected function close()
    {
        $this->path = null;
        $this->imageResolver->getProcessor()->close();
    }

    /**
     * apply
     *
     *
     * @return void
     */
    protected function apply()
    {
        list ($source, $params, $filter) = $this->paramsToString();

        $fragments = $this->getParamString($params, $source, $filter);

        $cr = $this->imageResolver->getCacheResolver();
        $path = $this->getCurrentPath();

        if (null === $cr || !$cache = $cr->resolve($path)) {
            return $this->getUri($path, $fragments);
        }

        return $this->resolveFromCache($cache, $fragments, $source, $params, $filter);
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
    protected function resolveFromCache(CacheInterface $cache, $fragments, $source, $params, $filter)
    {
        $path = $this->getCurrentPath();
        $src  = $this->getPath($this->imageResolver->getPathResolver()->resolve($path), $source);

        // If the image is not cached yet, this is the only time the processor
        // is invoked:
        if (!$cache->has($key = $this->createCacheKey($cache, $src, $params, $filter))) {
            $this->process($cache, $src, $key);
        }

        if (!isset($this->pool[$key])) {

            $cached = $cache->get($key);

            $file = $cached->getFileName();
            $dir  = basename(dirname($cached->getPath()));

            var_dump($path);
            var_dump($file);
            var_dump($dir);

            $this->pool[$key] = '/'. implode('/', [$path, $this->cacheSuffix, $dir, $file]);
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
    protected function process(CacheInterface $cache, $src, $key)
    {
        $proc = $this->imageResolver->getProcessor();

        if (null === $loader = $this->imageResolver->getLoaderResolver()->resolve($this->getCurrentPath())) {
            throw new \InvalidArgumentException;
        }

        if (!$loader->supports($src)) {
            throw new \InvalidArgumentException;
        }

        $proc->load($loader->load($src));
        $proc->process($this->compileExpression());

        $cache->set($key, $proc);

        $this->close();
    }

    /**
     * compileExpression
     *
     * @return array
     */
    protected function compileExpression()
    {
        return array_merge($this->parameters->all(), ['filter' => $this->filters->toArray()]);
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
     * @return string
     */
    private function getCurrentPath()
    {
        return $this->path ?: $this->defaultPath;
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
     * compileExpression
     *
     * @access protected
     * @return array
     */
    protected function paramsToString()
    {
        $filters = $this->filters->toArray();

        return [
            $this->source,
            $this->parameters->asString(),
            !empty($filters) ? sprintf('filter:%s', $this->filters->compile()) : null
        ];
    }
}
