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
 *
 * @package
 * @version
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
     * filters
     *
     * @var array
     */
    protected $filters;

    /**
     * __construct
     *
     * @param DriverInterface $driver
     * @access public
     * @return mixed
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        $this->filters = [];
    }

    /**
     * size
     *
     * @param mixed $source
     * @param mixed $width
     * @param mixed $height
     * @access public
     * @return mixed
     */
    public function source($source)
    {
        $this->clean();
        $this->source = $source;
        return $this;
    }

    /**
     * resize
     *
     * @param mixed $gravity
     * @access public
     * @return mixed
     */
    protected function callResize($width, $height)
    {
        $this->mode = 'resize';
        $this->targetSize = [$width, $height];
        $this->arguments = [];
    }

    /**
     * crop
     *
     * @param mixed $gravity
     * @param mixed $background
     * @access public
     * @return mixed
     */
    protected function callCrop($width, $height, $gravity, $background = null)
    {
        $this->mode = 'crop';
        $this->targetSize = [$width, $height];
        $this->arguments = [$gravity, $background];
    }

    /**
     * cropAndResize
     *
     * @param mixed $gravity
     * @access public
     * @return mixed
     */
    protected function callCropAndResize($width, $height, $gravity)
    {
        $this->mode = 'cropResize';
        $this->targetSize = [$width, $height];
        $this->arguments = [$gravity];
    }

    /**
     * fit
     *
     * @access public
     * @return mixed
     */
    protected function callFit($width, $height)
    {
        $this->mode = 'resizeToFit';
        $this->targetSize = [$width, $height];
        $this->arguments = [];
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return mixed
     */
    public function filter($name, $options = null) {

        $this->filters[$name] = $options;
        return $this;
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return mixed
     */
    public function filters(array $filters) {

        foreach($filters as $name => $options) {
            $this->filter($name, $options);
        }
        return $this;
    }

    /**
     * process
     *
     * @access protected
     * @return mixed
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
            $src = $this->resolver->getCachedUrl($image);
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
     * @return mixed
     */
    protected function compileExpression()
    {
        $parts = [$this->getMode()];

        foreach ($this->targetSize as $value) {

            if (is_numeric($value)) {
                $parts[] = (string)$value;
            }
        }

        foreach ($this->arguments as $i => $arg) {

            if (is_numeric($arg) || ($i === 1 and $this->isColor($arg))) {
                $parts[] = trim((string)$arg);
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
     * @return mixed
     */
    private function compileFilterExpression()
    {
        $filters = [];

        foreach ($this->filters as $filter => $options) {
            $opt = [];

            if (is_array($options)) {

                foreach  ($options as $option => $value) {
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
        default:
            return 0;
        }
    }
}
