<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use \Thapp\Image\AbstractImage;
use \Thapp\Image\ProcessorInterface;
use \Thapp\Image\Cache\CacheInterface;
use \Thapp\Image\Filter\FilterExpression;
use \Thapp\JitImage\Resource\ImageResource;
use \Thapp\JitImage\Resolver\PathResolver;
use \Thapp\JitImage\Resolver\ImageResolver;
use \Thapp\JitImage\Resolver\ImageResolverHelper;
use \Thapp\JitImage\Resolver\RecipeResolver;
use \Thapp\Image\Driver\Parameters;

/**
 * @class Image extends BaseImage
 *
 * @see BaseImage
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class JitImage extends AbstractImage
{
    use ImageResolverHelper;

    private $path;
    private $pool;
    private $recipes;

    /**
     * @param ImageResolver $imageResolver
     */
    public function __construct(
        ImageResolver $resolver,
        PathResolver $pathResolver,
        RecipeResolver $recipes,
        $cSuffix = 'cached',
        $dPath = null
    ) {
        $this->pool        = [];
        $this->paths       = $pathResolver;
        $this->recipes     = $recipes;
        $this->resolver    = $resolver;
        $this->cacheSuffix = $cSuffix;
        $this->defaultPath = $dPath;

        $this->filters     = new FilterExpression([]);
        $this->parameters  = new Parameters;
    }

    public static function create($source = null, $driver = self::DRIVER_IMAGICK)
    {
        throw new \BadMethodCallException('calling create is not allowed on this intance');
    }

    /**
     * Bakes an image from a recipe.
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

        list ($params, $filter) = $res;

        if (null !== $filter) {
            $this->filter($filter);
        }

        $this->parameters->setFromString($params);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get()
    {
        $this->parameters->setMode(ProcessorInterface::IM_NOSCALE);
        $this->parameters->setTargetSize();

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function pixel($pixel)
    {
        parent::pixel($pixel);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function resize($width, $height)
    {
        parent::resize($width, $height);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function scale($percent)
    {
        parent::scale($percent);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function crop($width, $height, $gravity = 5, $background = null)
    {
        parent::crop($width, $height, $gravity, $background);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function fit($width, $height)
    {
        parent::fit($width, $height);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @param string  $path
     *
     * @return Image
     */
    public function from($path)
    {
        $this->close();

        $this->path = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function cropAndResize($width, $height, $gravity)
    {
        parent::cropAndResize($width, $height, $gravity);

        return $this->process();
    }

    /**
     * process
     *
     * @return string
     */
    protected function process()
    {
        list ($source, $params, $filter) = $this->paramsToString();

        $fragments = $this->getParamString($params, $source, $filter);

        if (!$cache = $this->resolver->getCacheResolver()->resolve($this->getCurrentPath())) {
            return $this->getUri($fragments);
        }

        return $this->resolveFromCache($cache, $fragments, $source, $params, $filter);
    }

    private function getParamString($params, $source, $filter = null)
    {
        return null !== $filter ? implode('/', [$params, $source, $filter]) : implode('/', [$params, $source]);
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
        $src  = $this->getPath($this->paths->resolve($path), $source);

        // If the image is not cached yet, this is the only time the processor
        // is invoked:
        if (!$cache->has($key = $this->createCacheKey($cache, $src, $params, $filter))) {

            $processor = $this->resolver->getProcessor();
            $processor->load($src);
            $processor->process($p = $this->compileExpression());

            $cache->set($key, $processor);

            $this->close();
        }

        if (isset($this->pool[$key])) {
            return $this->pool[$key];
        }

        $cached = $cache->get($key);

        $file = $cached->getFileName();
        $dir  = basename(dirname($cached->getPath()));

        return $this->pool[$key] = '/'. implode('/', [$path, $this->cacheSuffix, $dir, $file]);
    }

    /**
     * compileExpression
     *
     * @return array
     */
    protected function compileExpression()
    {
        $params = parent::compileExpression();

        return array_merge($params, ['filter' => $this->filters->toArray()]);
    }

    /**
     * getImageFingerPrint
     *
     * @return void
     */
    protected function close()
    {
        parent::close();

        $this->path = null;
        $this->cache = null;
    }

    protected function getProcessor()
    {
        return $this->resolver->getProcessor();
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

    /**
     * getFileExtension
     *
     * @return string
     */
    private function getFileExtension($mime)
    {
        if ('image/jpeg' === $mime) {
            return 'jpg';
        }

        return explode('/', $mime)[1];
    }

    /**
     * @return string
     */
    private function getCurrentPath()
    {
        return $this->path ?: $this->defaultPath;
    }

    /**
     * getUri
     *
     * @param array $fragments
     *
     * @return string
     */
    private function getUri($fragments)
    {
        return '/' . implode('/', [trim($this->getCurrentPath(), '/'), $fragments]);
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
}
