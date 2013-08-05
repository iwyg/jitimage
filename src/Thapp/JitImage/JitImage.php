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

/**
 * Class: JitImage
 *
 * An adapter to easily utilize Image and ImageResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImage
{
    /**
     * driver
     *
     * @var mixed
     */
    protected $resolver;

    /**
     * targetSize
     *
     * @var mixed
     */
    protected $targetSize;

    /**
     * mode
     *
     * @var mixed
     */
    protected $mode;

    /**
     * base
     *
     * @var string
     */
    protected $base;

    /**
     * filters
     *
     * @var array
     */
    protected $filters;

    /**
     * Creates new JitImage object.
     *
     * @param DriverInterface $driver
     * @access public
     */
    public function __construct(ResolverInterface $resolver, $base = '/')
    {
        $this->resolver = $resolver;
        $this->filters = [];
        $this->base = $base;
    }

    /**
     * source
     *
     * @param string $source
     *
     * @access public
     * @return \Thapp\JitImage\JitImage
     */
    public function source($source)
    {
        $this->clean();
        $this->source = $source;

        return $this;
    }

    /**
     * callResize
     *
     * @param int $width
     * @param int $height
     *
     * @access protected
     * @return void
     */
    protected function callResize($width, $height)
    {
        $this->mode = 'resize';
        $this->targetSize = [$width, $height];
        $this->arguments = [];
    }

    /**
     * callCrop
     *
     * @param int    $width
     * @param int    $height
     * @param int    $gravity
     * @param string $background
     *
     * @access protected
     * @return void
     */
    protected function callCrop($width, $height, $gravity, $background = null)
    {
        $this->mode = 'crop';
        $this->targetSize = [$width, $height];
        $this->arguments = [$gravity, $background];
    }

    /**
     * callCropAndResize
     *
     * @param int $width
     * @param int $height
     * @param int $gravity
     *
     * @access protected
     * @return void
     */
    protected function callCropAndResize($width, $height, $gravity)
    {
        $this->mode = 'cropResize';
        $this->targetSize = [$width, $height];
        $this->arguments = [$gravity];
    }

    /**
     * callFit
     *
     * @param int $width
     * @param int $height
     *
     * @access protected
     * @return void
     */
    protected function callFit($width, $height)
    {
        $this->mode = 'resizeToFit';
        $this->targetSize = [$width, $height];
        $this->arguments = [];
    }

    /**
     * callScale
     *
     * @param int $percent
     *
     * @access protected
     * @return void
     */
    protected function callScale($percent)
    {
        $this->mode = 'percentualScale';
        $this->targetSize = [$percent, null];
        $this->arguments = [];
    }

    /**
     * pixel limit
     *
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return void
     */
    protected function callPixel($pixel)
    {
        $this->mode = 'resizePixelCount';
        $this->targetSize = [$pixel, null];
        $this->arguments = [];
    }

    /**
     * get the unprocessed image
     *
     * @access protected
     * @return void
     */
    protected function callGet()
    {
        if ($this->targetSize) {
            throw new \InvalidArgumentException('can\'t get original iamge if target size is already set');
        }
        $this->mode = 'default';
        $this->arguments = [];
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return \Thapp\JitImage\JitImage
     */
    public function filter($name, $options = null)
    {
        $this->filters[$name] = $options;

        return $this;
    }

    /**
     * filters
     *
     * @param array $filters
     *
     * @access public
     * @return \Thapp\JitImage\JitImage
     */
    public function filters(array $filters)
    {

        foreach ($filters as $name => $options) {
            $this->filter($name, $options);
        }

        return $this;
    }

    /**
     * process
     *
     * @access protected
     * @return string
     */
    protected function process()
    {
        extract($this->compileExpression());

        $this->clean();
        $this->resolver->close();

        $this->resolver->setParameter($params);
        $this->resolver->setSource($source);
        $this->resolver->setFilter($filter);

        if ($image = $this->resolver->getCached()) {
            $src = $this->base.$this->resolver->getCachedUrl($image);
            $this->resolver->close();
            $image->close();

            return $src;
        }

        if ($image = $this->resolver->resolve($image)) {
            $src = $this->resolver->getImageUrl($image);
            $this->resolver->close();
            $image->close();

            return $src;
        }

        $this->resolver->close();

        return;
    }

    /**
     * compileExpression
     *
     * @access protected
     * @return array
     */
    protected function compileExpression()
    {
        $parts = [$this->getMode()];

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
        $filter = $this->compileFilterExpression();

        return compact('source', 'params', 'filter');
    }

    /**
     * compileFilterExpression
     *
     * @access private
     * @return string|null
     */
    private function compileFilterExpression()
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
             return sprintf('filter:%s', implode(':', $filters));
        }

        return null;
    }

    /**
     * clean
     *
     * @access private
     * @return void
     */
    private function clean()
    {
        $this->mode       = null;
        $this->source     = null;
        $this->filters    = [];
        $this->targetSize = [];
    }

    /**
     * isColor
     *
     * @param mixed $color
     * @access private
     * @return boolean
     */
    private function isColor($color)
    {
        return preg_match('#^[0-9a-fA-F]{3}|^[0-9a-fA-F]{6}#', $color);
    }

    /**
     * __call
     *
     * @param mixed $method
     * @param mixed $arguments
     * @access public
     * @return string
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this, $m = sprintf('call%s', ucfirst($method)))) {
            call_user_func_array([$this, $m], $arguments);

            return $this->process();
        }

        throw new \BadMethodCallException(sprintf('call to undefined method [%s]', $method));
    }

    /**
     * getMode
     *
     * @access public
     * @return int
     */
    protected function getMode()
    {
        switch ($this->mode) {
            case 'resize':
                return 1;
            case 'cropResize':
                return 2;
            case 'crop':
                return 3;
            case 'resizeToFit':
                return 4;
            case 'percentualScale':
                return 5;
            case 'resizePixelCount':
                return 6;
            default:
                return 0;
        }
    }
}
