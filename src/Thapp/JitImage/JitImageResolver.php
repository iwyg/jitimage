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

use \Thapp\JitImage\Cache\CachedImage;
use \Thapp\JitImage\Cache\CacheInterface;

/**
 * Image resolver
 *
 * @implements ResolverInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageResolver implements ResolverInterface
{
    /**
     * image
     *
     * @var \Thapp\JitImage\ImageInterface
     */
    protected $image;

    /**
     * input
     *
     * @var array
     */
    protected $input = [];

    /**
     * parameter
     *
     * @var mixed
     */
    protected $parameter = [];

    /**
     * processCache
     *
     * @var mixed
     */
    protected $processCache;

    /**
     * cachedNames
     *
     * @var mixed
     */
    protected $cachedNames;

    /**
     * create a new instance of \Thapp\JitImage\JitImageResolver
     *
     * @param \Thapp\JitImage\ResolverConfigInterface  $config
     * @param \Thapp\JitImage\ImageInterface           $image
     * @param \Thapp\JitImage\Cache\CacheInterface     $cache
     *
     * @access public
     */
    public function __construct(ResolverConfigInterface $config, ImageInterface $image, CacheInterface $cache)
    {
        $this->image        = $image;
        $this->config       = $config;
        $this->processCache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function setResolveBase($base = '/')
    {
        return $this->config->set('base', $base);
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($parameter)
    {
        $this->input['parameter'] = $parameter;
    }

    /**
     * {@inheritDoc}
     */
    public function setSource($source)
    {
        $this->input['source'] = $source;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilter($filter = null)
    {
        $this->input['filter'] = $filter;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($key = null)
    {
        if (is_null($key)) {
            return $this->parameter;
        }
        return isset($this->parameter[$key]) ? $this->parameter[$key] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve()
    {
        $this->image->close();

        if (!$this->canResolve()) {
            return false;
        }

        $this->parseAll();

        if ($this->config->cache &&
            $image = $this->resolveFromCache(
                $id = $this->getImageRequestId($this->getInputQuery(), $this->input['source'])
            )
        ) {
            return $image;
        }

        // something went wrong
        if (!$img = $this->isReadableFile($this->parameter)) {
            return false;
        }

        // something went wrong
        if (!$this->image->load($img)) {
            return false;
        }

        $this->image->process($this);


        if ($this->config->cache) {
            $this->processCache->put($id, $this->image->getContents());
        }

        return $this->image;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        $this->input = [];
        $this->parameter = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getCached()
    {
        if (!$this->canResolve()) {
            return false;
        }

        if (!$this->config->cache) {
            return false;
        }

        $this->resolve();
        return $this->resolveFromCache($this->getImageRequestId($this->getInputQuery(), $this->input['source']));
    }

    /**
     * {@inheritDoc}
     */
    public function getCachedUrl(ImageInterface $cachedImage)
    {
        return sprintf(
            '/%s/%s',
            $this->config->cache_route,
            $this->processCache->getRelPath($cachedImage->getSource())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getImageUrl(ImageInterface $image)
    {
        if (false !== (strpos($image->getSource(), $this->config->base))) {
            $base = substr($image->getSource(), strlen($this->config->base));
            $input = $this->input;

            return sprintf(
                '/%s',
                trim(implode('/', [$this->config->base_route, $input['parameter'], trim($base, '/'),
                $input['filter']]), '/')
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolveFromCache($id)
    {
        $id = preg_replace('~(\.(jpe?g|gif|png|webp))$~', null, $this->processCache->getIdFromUrl($id));

        if ($this->processCache->has($id)) {
            //$image->close();
            $image = $this->processCache->get($id);
            return $image;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function disableCache()
    {
        $this->config->set('cache', false);
    }

    /**
     * determine if all params are set
     *
     * @access protected
     * @return bool
     */
    protected function canResolve()
    {
        return is_array($this->input)
            && array_key_exists('parameter', $this->input)
            && array_key_exists('source', $this->input)
            && array_key_exists('filter', $this->input);
    }

    /**
     * parse input parameter
     *
     * @access protected
     * @return void
     */
    protected function parseAll()
    {
        if (!empty($this->parameter)) {
            return $this->parameter;
        }

        $this->parseParameter();
        $this->parseSource();
        $this->parseFilter();
    }

    /**
     * get the input query as a string
     *
     * @access protected
     * @return string
     */
    protected function getInputQuery()
    {
        return implode('/', array_values($this->input));
    }

    /**
     * parse input parameter
     *
     * @param string       $mode
     * @param string|null  $width
     * @param string|null  $height
     * @param string|null  $gravity
     * @param string       $source
     *
     * @access protected
     * @return void
     */
    protected function parseParameter()
    {
        list ($mode, $width, $height, $gravity, $background) = array_pad(
            preg_split(
                '%/%',
                $this->input['parameter'],
                -1,
                PREG_SPLIT_NO_EMPTY
            ),
            5,
            null
        );

        return $this->setParameterValues(
            (int)$mode,
            ((int)$mode !== 1 && (int)$mode !== 2) ? $this->getIntVal($width) : (int)$this->getIntVal($width),
            ((int)$mode !== 1 && (int)$mode !== 2) ? $this->getIntVal($height) : (int)$this->getIntVal($height),
            $this->getIntVal($gravity),
            $background
        );
    }

    /**
     * setParameterValues
     *
     * @param int    $mode
     * @param int    $width
     * @param int    $height
     * @param int    $gravity
     * @param string $background
     *
     * @access protected
     * @return void
     */
    protected function setParameterValues($mode, $width = null, $height = null, $gravity = null, $background = null)
    {
        $parameter = compact('mode', 'width', 'height', 'gravity', 'background');
        $this->parameter = array_merge($this->parameter, $parameter);
    }


    /**
     * parse the input source parameter
     *
     * @access protected
     * @return mixed
     */
    protected function parseSource()
    {
        $this->parameter['source'] = $this->input['source'];
    }

    /**
     * parse the filter input parameter
     *
     * @access protected
     * @return mixed
     */
    protected function parseFilter()
    {
        if (isset($this->input['filter'])) {
            $fragments  = preg_split('%:%', $this->input['filter'], -1, PREG_SPLIT_NO_EMPTY);
            $this->parameter['filter'] = $this->parseImageFilter($fragments);

            return;
        }

        $this->parameter['filter'] = [];
    }

    /**
     * getIntVal
     *
     * @param mixed $value
     * @access protected
     * @return int|null
     */
    protected function getIntVal($value = null)
    {
        return is_null($value) ? $value : (int)$value;
    }

    /**
     * getImageSource
     *
     * @param mixed $source
     * @param array $parameter
     * @access protected
     * @return mixed
     */
    protected function getImageSource($source, array &$parameter)
    {
        $fragments  = preg_split('%:%', $source, -1, PREG_SPLIT_NO_EMPTY);
        $parameter['source'] = array_shift($fragments);
        $this->parseImageFilter($fragments, $parameter);
    }

    /**
     * parseImageFilter
     *
     * @param array $filters
     * @param array $parameter
     * @access protected
     * @return void
     */
    protected function parseImageFilter(array $filters)
    {
        $parameter = [];

        if ('filter' !== array_shift($filters)) {
            return;
        }

        foreach ($filters as $filter) {
            $this->getFilterParams($parameter, $filter);
        }

        return $parameter;
    }

    /**
     * getFilterParams
     *
     * @param mixed $filter
     * @access protected
     * @return void
     */
    protected function getFilterParams(array &$filters, $filter)
    {
        $fragments = preg_split('%;%', $filter, -1, PREG_SPLIT_NO_EMPTY);

        $name = array_shift($fragments);
        $params = [];

        foreach ($fragments as $param) {
            list($key, $value)  = explode('=', $param);
            $params[$key] = $value;
        }

        $filters[$name] = $params;
    }

    /**
     * extract a possible color string from the parameter input
     *
     * @param array $parameter
     * @access protected
     * @return void
     */
    protected function getOptionalColor(array &$parameter)
    {
        preg_match('/^[0-9A-Fa-f]{3,6}/', $parameter['source'], $color);

        $length = strpos($parameter['source'], '/');

        $hasColor = (6 === $length && 3 === $length) && $length === strlen(current($color));

        if (!empty($color)) {
            $parameter['source'] = substr($parameter['source'], strlen(current($color)));
        }

        if ($hasColor) {
            $parameter['background'] = '#' . current($color);
        }
    }

    /**
     * returns the image cache id string
     *
     * @param mixed $requestString
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return string
     */
    protected function getImageRequestId($requestString, $source = null)
    {
        if (!isset($this->cachedNames[$requestString])) {

            $this->cachedNames[$requestString] = $this->processCache->createKey(
                $source,
                $requestString,
                $this->config->cache_prefix,
                pathinfo($source, PATHINFO_EXTENSION)
            );
        }

        return $this->cachedNames[$requestString];
    }

    /**
     * getProcessedCacheName
     *
     * @param ImageInterface $image
     * @param string $requestString
     * @param string $srouce
     *
     * @access protected
     * @return string
     */
    protected function getProcessedCacheId(ImageInterface $image, $requestString, $source)
    {

        $osuffix = $image->getSourceFormat();
        $psuffix = $image->getFileFormat();

        unset($this->cachedNames[$requestString]);

        $this->cachedNames[$requestString] = $this->processCache->createKey(
            $source.$osuffix.$psuffix,
            $requestString,
            $this->config->cache_prefix,
            $psuffix
        );

        return $this->cachedNames[$requestString];
    }

    protected function getOutputTypeFromFilter($source)
    {
        if (($filter = $this->getParameter('filter')) && isset($filter[$format = $this->config->format_filter])) {
            return current(array_values($filter[$format]));
        }

        return pathinfo($source, PATHINFO_EXTENSION);
    }

    /**
     * isReadableFile
     *
     * @access protected
     * @return string|boolean
     */
    protected function isReadableFile(array $parameter)
    {
        extract($parameter);

        if (null !== parse_url($source, PHP_URL_SCHEME)) {
            return $this->isValidDomain($source);
        }

        if (is_file($file = $this->config->base . '/' . $source)) {
            return $file;
        }
        return false;
    }

    /**
     * isValidDomain
     *
     * @access protected
     * @return string|boolean
     */
    protected function isValidDomain($url)
    {
        $trusted = $this->config->trusted_sites;


        if (!empty($trusted)) {
            extract(parse_url($url));

            $host = substr($url, 0, strpos($url, $host)).$host;

            if (!$this->matchHost($host, $trusted)) {
                return false;
            }
        }
        return $url;
    }

    /**
     * matchHosts
     *
     * @param mixed $host
     * @param array $hosts
     *
     * @access protected
     * @return boolean
     */
    protected function matchHost($host, array $hosts)
    {
        foreach ($hosts as $trusted) {
            if (0 === strcmp($host, $trusted) || preg_match('#^'. $trusted .'#s', $host)) {
                return true;
            }
        }
        return false;
    }
}
