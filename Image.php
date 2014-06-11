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

use \Thapp\Image\ProcessorInterface;
use \Thapp\Image\Image as BaseImage;
use \Thapp\JitImage\Resource\ImageResource;
use \Thapp\JitImage\Resolver\UrlResolver;
use \Thapp\JitImage\Resolver\PathResolver;
use \Thapp\JitImage\Resolver\ImageResolver;
use \Thapp\JitImage\Resolver\ImageResolverHelper;
use \Thapp\JitImage\Resolver\ParameterResolverInterface;
use \Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @class Image extends BaseImage
 *
 * @see BaseImage
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class Image extends BaseImage
{
    use ImageResolverHelper;

    /**
     * @param ImageResolver $imageResolver
     */
    public function __construct(ImageResolver $resolver, PathResolver $pathResolver, $cacheSuffix = 'cached')
    {
        $this->resolver = $resolver;
        $this->paths = $pathResolver;
        $this->cacheSuffix = $cacheSuffix;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get()
    {
        $this->mode = ProcessorInterface::IM_NOSCALE;
        $this->setTargetSize();
        $this->setArguments([]);

        return $this->process();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function pixel($pixel)
    {
        parent::pixel($pixle);

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
     * @param boolean $addExtension
     *
     * @return Image
     */
    public function from($path, $addExtension = false)
    {
        $this->close();

        $this->addExtension = (bool)$addExtension;

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

        $fragments = implode('/', [$params, $source, $filter]);

        if (!$cache = $this->resolver->getCacheResolver()->resolve($this->path)) {
            return $this->getUri($fragments);
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
    protected function resolveFromCache($cache, $fragments, $source, $params, $filter)
    {
        $this->cache = $cache;

        $path = $this->path;
        $src = $this->getPath($this->paths->resolve($this->path), $source);
        $key = $cache->createKey($src, $params.'/'.$filter, PATHINFO($src, PATHINFO_EXTENSION));

        // If the image is not cached yet, this is the only time the processor
        // is invoked:
        if (!$cache->has($key)) {

            $processor = $this->resolver->getProcessor();
            $processor->load($src);
            $processor->process($this->compileExpression());

            $cache->set($key, $processor->getContents());

            $this->close();
        }

        $extension = $this->addExtension ? '.'.$this->getFileExtension($cache->get($key)->getMimeType()) : '';

        return '/'. implode('/', [$path, $this->cacheSuffix, strtr($key, ['.' => '/'])]).$extension;
    }

    protected function compileExpression()
    {
        $params = parent::compileExpression();

        return array_merge($params, ['filter' => $this->filters]);
    }

    /**
     * getUri
     *
     * @param mixed $uri
     *
     * @access protected
     * @return string
     */
    protected function getUri($fragments)
    {
        return '/' . implode('/', [trim($this->path, '/'), $fragments]);
    }

    /**
     * getImageFingerPrint
     *
     *
     * @access protected
     * @return mixed
     */
    protected function getImageFingerPrint(array $params, array $filters)
    {
    }

    /**
     * getImageFingerPrint
     *
     *
     * @access protected
     * @return void
     */
    protected function close()
    {
        $this->resolver->getProcessor()->close();

        $this->filters   = [];
        $this->arguments = [];

        $this->source = null;
        $this->path = null;
        $this->cache = null;
    }

    /**
     * compileExpression
     *
     * @access protected
     * @return array
     */
    protected function paramsToString()
    {
        $parts = ['mode' => $this->mode];

        foreach ($this->targetSize as $value) {

            if (is_numeric($value)) {
                $parts[] = (string) $value;
            }
        }

        foreach ($this->arguments as $i => $arg) {

            if (is_numeric($arg) || ($i === 1 and $this->isColor($arg))) {
                $parts[] = trim((string) $arg);
            }
        }

        $source = $this->source;
        $params = implode('/', $parts);
        $filter = $this->filtersToString();

        return [$source, $params, $filter];
    }

    /**
     * compileFilterExpression
     *
     * @access private
     * @return string|null
     */
    private function filtersToString()
    {
        $filters = [];

        foreach ($this->filters as $filter => $options) {
            $opt = [];

            if (is_array($options)) {

                foreach ($options as $option => $value) {
                    $opt[] = sprintf('%s=%s', $option, $value);
                }
            }
            $filters[] = sprintf('%s;%s', $filter, implode(';', $opt));
        }
        if (!empty($filters)) {
             return rtrim(sprintf('filter:%s', implode(':', $filters)), ';');
        }

        return null;
    }

    protected function getFileExtension($mime)
    {
        if ('image/jpeg' === $mime) {
            return 'jpg';
        }

        return explode('/', $mime)[1];
    }
}
