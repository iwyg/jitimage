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

use Thapp\JitImage\Cache\CacheInterface;

/**
 * Class: ImageUrlResolver
 *
 * @implements ResolverInterface
 *
 * @package
 * @version
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
     * cache
     *
     * @var mixed
     */
    protected $cache = true;

    /**
     * __construct
     *
     * @param ImageInterface $image
     * @param CacheInterface $cache
     * @access public
     * @return mixed
     */
    public function __construct(ImageInterface $image, CacheInterface $cache)
    {
        $this->image        = $image;
        $this->processCache = $cache;
    }

    /**
     * resolvePath
     *
     * @param mixed $arguments
     * @access public
     * @return mixed
     */
    public function resolve()
    {
        if ($this->cache and $this->processCache->has($id = $this->getImageRequestId($this->getInputQuery()))) {
            return $this->processCache->get($id);
        }

        $this->parseParameter();
        $this->parseSource();
        $this->parseFilter();


        if (!$img = $this->isReadableFile($this->parameter)) {
            return false;
        }

        $this->image->load($img);
        $this->image->process($this);

        $contents = $this->image->getContents();
        $this->cache and $this->processCache->put($id, $contents);
        $this->image->close();
        return $contents;
    }

    /**
     * disableCache
     *
     * @param mixed $param
     * @access public
     * @return mixed
     */
    public function disableCache()
    {
        $this->cache = false;
    }

    /**
     * getImage
     *
     * @access public
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * setResolveBase
     *
     * @param string $base
     * @access public
     * @return mixed
     */
    public function setResolveBase($base = '/')
    {
        return $this->base = $base;
    }

    /**
     * setQuality
     *
     * @param mixed $param
     * @access public
     * @return mixed
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * getFilterVars
     *
     * @param mixed $filter
     * @access public
     * @return array
     */
    public function getFilterVars($filter = null)
    {
        if (!is_null($filter) && isset($this->parameter['filter'][$filter])) {
            return $this->params['filter'][$filter];
        }
        return $this->params['filter'];
    }

    /**
     * getParameter
     *
     * @param array $fragments
     * @access protected
     * @return array
     */
    public function setParameter($parameter)
    {
        $this->input['parameter'] = $parameter;
    }

    /**
     * setSource
     *
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function setSource($source)
    {
        $this->input['source'] = $source;
    }

    /**
     * setFilter
     *
     * @param mixed $filter
     * @access public
     * @return mixed
     */
    public function setFilter($filter = null)
    {
        $this->input['filter'] = $filter;
    }

    /**
     * getInputQuery
     *
     * @access protected
     * @return mixed
     */
    protected function getInputQuery()
    {
        return implode('/', array_values($this->input));
    }
    /**
     * parseParameter
     *
     * @param mixed $mode
     * @param mixed $width
     * @param mixed $height
     * @param mixed $gravity
     * @param mixed $source
     * @access protected
     * @return void
     */
    protected function parseParameter()
    {
        list ($mode, $width, $height, $gravity, $background) = array_pad(
            preg_split('%/%', $this->input['parameter'], -1, PREG_SPLIT_NO_EMPTY),
        5, null);

        return $this->setParameterValues(
            (int)$mode,
            ((int)$mode !== 1 and (int)$mode !== 2) ? $this->getIntVal($width) : (int)$this->getIntVal($width),
            ((int)$mode !== 1 and (int)$mode !== 2) ? $this->getIntVal($height) : (int)$this->getIntVal($height),
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
     * parseSource
     *
     * @access protected
     * @return mixed
     */
    protected function parseSource()
    {
        $this->parameter['source'] = $this->input['source'];
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
     * parseFilter
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
     * getParameter
     *
     * @access public
     * @return mixed
     */
    public function getParameter($key = null)
    {
        if (is_null($key)) {
            return $this->parameter;
        }
        return isset($this->parameter[$key]) ? $this->parameter[$key] : null;
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
     * getOptionalColor
     *
     * @param mixed $source
     * @param array $parameter
     * @access protected
     * @return void
     */
    protected function getOptionalColor(array &$parameter)
    {
        preg_match('/^[0-9A-Fa-f]{3,6}/', $parameter['source'], $color);
        $length = strpos($parameter['source'], '/');

        $hasColor = (6 === $length || 3 === $length) and $length === strlen(current($color));

        if (!empty($color)) {
            $parameter['source'] = substr($parameter['source'], strlen(current($color)));
        }

        if ($hasColor) {
            $parameter['background'] = '#' . current($color);
        }
    }

    /**
     * getImageRequestId
     *
     * @param mixed $requestString
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return string
     */
    protected function getImageRequestId($requestString)
    {
        return sprintf('%s', hash('md5', $requestString));
    }

    /**
     * isReadableFile
     *
     * @access protected
     * @return mixed
     */
    protected function isReadableFile(array $parameter)
    {
        extract($parameter);

        if (preg_match('#^(https?://|spdy://|file://)#', $source, $matches)) {
            return $source;
        }

        if (is_file($file = $this->base . '/' . $source)) {
            return realpath($file);
        }

        return false;
    }
}
