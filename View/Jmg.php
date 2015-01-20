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
use Thapp\JitImage\Http\UrlResolverInterace;
use Thapp\JitImage\Resolver\ImageResolverHelper;
use Thapp\JitImage\Resolver\ImageResolverInterface;
use Thapp\JitImage\Resolver\RecipeResolverInterface;
use Thapp\JitImage\Http\UrlBuilderInterface;
use Thapp\JitImage\Resource\ResourceInterface;
use Thapp\JitImage\Resource\CachedResourceInterface;

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
    private $generator;
    private $url;
    private $imageResolver;
    private $defaultPath;
    private $cacheSuffix;
    private $current;
    private $asTag;
    private $attributes;

    /**
     * Constructor.
     *
     * @param ImageResolverInterface $imageResolver
     * @param RecipeResolverInterface $recipes
     * @param string $default
     * @param string $cPrefix
     */
    public function __construct(
        ImageResolverInterface $imageResolver,
        RecipeResolverInterface $recipes,
        UrlBuilderInterface $url,
        $default = '',
        $cacheSuffix = 'cached'
) {
        $this->imageResolver = $imageResolver;
        $this->recipes = $recipes;
        $this->url = $url;
        $this->defaultPath = $default;
        $this->cacheSuffix = $cacheSuffix;

        $this->pool = [];
        $this->asTag = false;

        $this->start = microtime(true);
    }

    protected function stop()
    {
        return '?time='. microtime(true) - $this->start;
    }

    /**
     * Get the ImageResolver
     *
     * @return ImageResolverInterface
     */
    public function getImageResolver()
    {
        return $this->imageResolver;
    }

    /**
     * Get the RecipesResolver
     *
     * @return RecipesResolverInterface
     */
    public function getRecipesResolver()
    {
        return $this->recipes;
    }

    /**
     * Takes an image source stirng for manipulation.
     *
     * @param string $source the image source path
     * @param string $path the image base path
     *
     * @return Generator
     */
    public function take($source, $path = null, $asTag = false, array $attributes = [])
    {
        $this->current = null;
        $this->setAsTag($asTag, $attributes);

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
    public function make($recipe, $source, $asTag = false, array $attributes = [])
    {
        if (!$res = $this->recipes->resolve($recipe)) {
            return '';
        }

        $this->setAsTag($asTag, $attributes);

        list ($prefix, $params, $filter) = $res;

        return $this->apply(
            $prefix,
            $source,
            Parameters::fromString($params),
            $filter ? $this->filter($filter) : null,
            $recipe
        );
    }

    /**
     * apply
     *
     * @return void
     */
    public function apply($name, $source, Parameters $params, FilterExpression $filters = null, $recipe = null)
    {
        if (!$resource = $this->imageResolver->resolve($source, $params, $filters, $name)) {
            return '';
        }

        $this->current = $resource;

        if ($resource instanceof CachedResourceInterface) {
            return $this->getCachedPath($resource, $name);
        }

        if (null !== $recipe) {
            return $this->getRecipeUri($source, $name, $recipe, $params, $filters);
        }

        return $this->getUri($source, $name, $params, $filters);
    }

    /**
     * getUri
     *
     * @param mixed $source
     * @param mixed $name
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return string
     */
    private function getUri($source, $name, Parameters $params, FilterExpression $filters = null)
    {
        return $this->getOutput($this->url->getUri($source, $params, $filters, $name));
    }

    /**
     * getRecipeUri
     *
     * @param mixed $source
     * @param mixed $name
     * @param mixed $recipe
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return string
     */
    private function getRecipeUri($source, $name, $recipe, Parameters $params, FilterExpression $filters = null)
    {
        return $this->getOutput($this->url->getRecipeUri($source, $recipe, $params, $filters));
    }

    /**
     * getCachedPath
     *
     * @param CachedResourceInterface $cached
     * @param mixed $name
     *
     * @return string
     */
    private function getCachedPath(CachedResourceInterface $cached, $name)
    {
        return $this->getOutput($this->url->getCachedUri($cached, $name, $this->cacheSuffix));
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

    protected function setAsTag($asTag, array $attributes)
    {
        if (!$asTag) {
            $this->clearTag();

            return;
        }

        $this->asTag = true;
        $this->attributes = $attributes;
    }

    protected function getOutput($path)
    {
        if ($this->asTag) {
            return $this->createTag($path, array_merge($this->attributes, $this->getResourceDimension()));
        }

        return $path;
    }

    protected function getResourceDimension()
    {
        return ['width' => $this->current->getWidth(), 'height' => $this->current->getHeight()];
    }

    protected function clearTag()
    {
        $this->asTag = false;
        $this->attributes = null;
    }

    private function createTag($path, array $attributes)
    {
        $parts = '';
        foreach ($attributes as $attribute => $value) {
            $parts .= sprintf('%s="%s" ', $attribute, $value);
        }

        return sprintf('<img src="%s" %s/>', $path, $parts);
    }
}
