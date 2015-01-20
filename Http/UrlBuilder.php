<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Http;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;
use Thapp\JitImage\Cache\CacheInterface;
use Thapp\JitImage\Resource\CachedResourceInterface;
use Thapp\JitImage\Resolver\RecipeResolverInterface;

/**
 * @class UrlBuilder
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlBuilder implements UrlBuilderInterface
{
    private $signer;
    private $cachePrefix;

    /**
     * Constructor.
     *
     * @param HttpSingerInterface $signer
     */
    public function __construct(HttpSignerInterface $signer = null)
    {
        $this->signer  = $signer;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri($source, Parameters $params, FilterExpression $filters = null, $prefix = '')
    {
        $path = $this->createImageUri($source, $params, $filters, $prefix);

        if (null !== $this->signer) {
            return $this->signer->sign($path, $params, $filters);
        }

        return $path;
    }

    public function getRecipeUri($source, $recipe, Parameters $params, FilterExpression $filters = null)
    {
        $path = $this->createRecipeUri($recipe, $source);

        if (null !== $this->signer) {
            return $this->signer->sign($path, $params, $filters);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedUri(CachedResourceInterface $resource, $name, $prefix)
    {
        $basePath = strtr($resource->getKey(), ['.' => '/']);

        return sprintf(
            '/%s/%s/%s%s',
            $prefix,
            $name,
            $this->getCachedPathBasePath($resource),
            $this->getCachedPathExtension($resource)
        );
    }

    /**
     * createImageUri
     *
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $prefix
     *
     * @return string
     */
    protected function createImageUri($source,  Parameters $params, FilterExpression $filters = null, $prefix = '')
    {
        return '/'.sprintf('%s/%s/%s', trim($prefix, '/'), (string)$params, $source, $this->getFiltersAsString($filters));
    }

    /**
     * getFiltersAsString
     *
     * @param FilterExpression $filters
     *
     * @return void
     */
    protected function getFiltersAsString(FilterExpression $filters = null)
    {
        if (null !== $filters && 0 < count($filters->all())) {
            return sprintf('/filter:%s', (string)$filter);
        }

        return '';
    }

    /**
     * getCachedPathBasePath
     *
     * @param CachedResourceInterface $resource
     *
     * @return string
     */
    protected function getCachedPathBasePath(CachedResourceInterface $resource)
    {
        return strtr($resource->getKey(), ['.' => '/']);
    }

    /**
     * getCachedPathExtension
     *
     * @param CachedResourceInterface $resource
     *
     * @return string
     */
    protected function getCachedPathExtension(CachedResourceInterface $resource)
    {
        if ($extension = pathinfo($resource->getPath(), PATHINFO_EXTENSION)) {
            return '.'.$extension;
        }

        return '';
    }

    /**
     * createRecipeUri
     *
     * @param mixed $recipe
     * @param mixed $source
     *
     * @return void
     */
    protected function createRecipeUri($recipe, $source)
    {
        return '/'.trim($recipe, '/') . '/' . trim($source, '/');
    }

}
