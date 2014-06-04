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
     * resolve
     *
     * @param mixed $params
     *
     * @access public
     * @return mixed
     */
    public function resolveParameters(array $parameters)
    {
        list ($path, $params, $source, $filter) = array_pad($parameters, 4, null);

        $path = $this->getPath($path, $source);

        $key = null;

        if (null !== $this->cacheResolver &&
            $resource = $this->cacheResolver->resolve($key = $this->getCacheKey($path, $params, $filter))
        ) {
            return $resource;
        }

        $params = array_merge($this->extractParams($params), ['filter' => $this->extractFilters($filter)]);

        $this->validateParams($params);

        return $this->applyProcessor($path, $params, $key);
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

    /**
     * @param string $path
     * @param string $parameters
     * @param string $filters
     *
     * @return string
     */
    private function getCacheKey($path, $parameters, $filters)
    {
        return $this->cacheResolver->getCache()->createKey(
            $path,
            $parameters.'/'.$filters,
            null,
            pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    /**
     * applyProcessor
     *
     * @access private
     * @return Response
     */
    private function applyProcessor($source, array $params, $key = null)
    {
        $this->processor->load($source);
        $this->processor->process($params);

        if (null !== $key) {
            $this->cacheResolver->getCache()->set($key, $this->processor->getContents());

            return $this->cacheResolver->getCache()->get($key);
        }

        $resource = new ImageResource;

        $resource->setContents($this->processor->getContents());
        $resource->setFresh(!$this->processor->isProcessed());
        $resource->setLastModified($this->processor->getLastModTime());
        $resource->setMimeType($this->processor->getMimeType());

        // if the image was passed through, we can set a source path
        if (!$this->processor->isProcessed()) {
            $resource->setPath($this->processor->getSource());
        }

        return $resource;
    }

    /**
     * extractParams
     *
     * @param string $params
     *
     * @return array
     */
    private function extractParams($params)
    {
        list ($mode, $width, $height, $gravity, $background) = array_map(function ($value, $key = null) {
            return is_numeric($value) ? (int)$value : $value;
        }, array_pad(explode('/', $params), 5, null));

        $width  = ($mode !== 1 && $mode !== 2) ? $width : (int)$width;
        $height = ($mode !== 1 && $mode !== 2) ? $height : (int)$height;

        return compact('mode', 'width', 'height', 'gravity', 'background');
    }

    /**
     * getIntVal
     *
     * @param mixed $value
     * @access protected
     * @return int|null
     */
    private function getIntVal($value = null)
    {
        return null === $value ? $value : (int)$value;
    }

    /**
     * extractFilters
     *
     * @param mixed $filters
     *
     * @access private
     * @return mixed
     */
    private function extractFilters($filterStr = null)
    {
        $filters = [];

        if (null === $filterStr) {
            return $filters;
        }

        foreach (explode(',', substr($filterStr, 1+strpos($filterStr, ':'))) as $filter) {
            list ($key, $value) = $this->extractFilterParams($filter);
            $filters[$key] = $value;
        }

        return $filters;
    }

    /**
     * extractFilterParams
     *
     * @param mixed $paramStr
     *
     * @access private
     * @return array
     */
    private function extractFilterParams($paramStr)
    {
        $parts = explode(';', $paramStr);
        $key = array_shift($parts);

        $params = [];

        foreach ($parts as $part) {
            $options = explode('=', $part);
            $params[$options[0]] = $this->getFilterOptionValue($options[1]);
        }

        return [$key, $params];
    }

    /**
     * getFilterOptionValue
     *
     * @param mixed $value
     *
     * @access private
     * @return array
     */
    private function getFilterOptionValue($value)
    {
        if (is_numeric($value)) {
            return substr_count($value, '.') ? (float)$value : (int)$value;
        }

        return $value;
    }

    /**
     * getPath
     *
     * @return string
     */
    private function getPath($path, $source)
    {
        if (null !== parse_url($source, PHP_URL_SCHEME)) {
            return $source;
        };

        if (null !== parse_url($path, PHP_URL_PATH)) {
            return rtrim($path, '\\\/') . DIRECTORY_SEPARATOR .
                strtr($source, ['/' => DIRECTORY_SEPARATOR]);
        }

        return $path . '/' . $source;
    }
}
