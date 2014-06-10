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
    /**
     * @param ImageResolver $imageResolver
     */
    public function __construct(ImageResolver $imageResolver)
    {
        $this->resolver = $imageResolver;
    }

    /**
     * get
     *
     * @return string
     */
    public function get()
    {
        $this->mode = ProcessorInterface::IM_NOSCALE;
        $this->arguments = [];

        return $this->process();
    }

    /**
     * resize
     *
     * @return string
     */
    public function resize()
    {
        call_user_func_array(parent::resize, func_get_args());

        return $this->process();
    }

    /**
     * scale
     *
     * @return string
     */
    public function scale()
    {
        call_user_func_array(parent::scale, func_get_args());

        return $this->process();
    }

    /**
     * crop
     *
     * @return string
     */
    public function crop()
    {
        call_user_func_array(parent::crop, func_get_args());

        return $this->process();
    }

    /**
     * fit
     *
     * @return string
     */
    public function fit()
    {
        call_user_func_array(parent::fit, func_get_args());

        return $this->process();
    }

    /**
     * cropAndResize
     *
     * @return string
     */
    public function cropAndResize()
    {
        call_user_func_array(parent::cropAndResize, func_get_args());

        return $this->process();
    }

    protected function process()
    {
        if ($this->processor->isProcessed()) {
            return $this->getFileUrl();
        }

        $params = $this->compileExpression();

        if (null !== $this->cache && $this->cache->has($key = $this->getCacheKey($params, $this->filters))) {
            $this->loadFromCache($key);

            $resource = $this->cache->get($id);

        } else {
            $this->doProcess($params);
        }
    }

    protected function getFileUrl()
    {
    }
}
