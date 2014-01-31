<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Proxy;

use \Thapp\JitImage\ImageInterface;
use \Thapp\JitImage\ResolverInterface;

/**
 * @class ProxyImage extends Image
 * @see Image
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ProxyImage implements ImageInterface
{
    /**
     * initializer
     *
     * @var callable
     */
    protected $initializer;

    /**
     * invoked
     *
     * @var boolean
     */
    protected $invoked;

    /**
     * @param callable $initializer
     *
     * @access public
     */
    public function __construct(callable $initializer)
    {
        $this->invoked = false;
        $this->initializer = $initializer;
    }

    /**
     * @access public
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function load($source)
    {
        return $this->envokeObjectMethod('load', [$source]);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ResolverInterface $resolver)
    {
        return $this->envokeObjectMethod('process', [$resolver]);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuality($quality)
    {
        return $this->envokeObjectMethod('setQuality', [$quality]);
    }

    /**
     * {@inheritdoc}
     */
    public function setFileFormat($format)
    {
        return $this->envokeObjectMethod('setFileFormat', [$format]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileFormat()
    {
        return $this->envokeObjectMethod('getFileFormat');
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceMimeTime()
    {
        return $this->envokeObjectMethod('getSourceMimeTime');
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->envokeObjectMethod('getMimeType');
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->envokeObjectMethod('getSource');
    }

    /**
     * {@inheritdoc}
     */
    public function isProcessed()
    {
        return $this->envokeObjectMethod('isProcessed');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModTime()
    {
        return $this->envokeObjectMethod('getLastModTime');
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFormat()
    {
        return $this->envokeObjectMethod('getSourceFormat');
    }

    /**
     * It is likely that the close method is called before an actuall
     * interaction with the image's driver was initialized.
     * Therefor we will call close on the original Image once it was invoked by
     * a dirrerent method call.
     *
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->invoked) {
            return $this->envokeObjectMethod('close');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->envokeObjectMethod('getContents');
    }

    /**
     * @access private
     * @return mixed
     */
    private function envokeObjectMethod($method, array $arguments = [])
    {

        if (!$this->invoked) {
            $this->invokeInitializer();
        }

        return call_user_func_array([$this->object, $method], $arguments);
    }

    /**
     * @access private
     */
    private function invokeInitializer()
    {
        $this->invoked = true;
        $this->object = call_user_func($this->initializer);
    }
}
