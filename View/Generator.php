<?php

/*
 * This File is part of the Thapp\JitImage\View package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\View;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;
use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Resource\CachedResource;

/**
 * @class Generator
 *
 * @package Thapp\JitImage\View
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Generator
{
    protected $jmg;
    protected $path;
    protected $source;
    protected $filters;
    protected $parameters;

    public function __construct(Jmg $jmg)
    {
        $this->jmg = $jmg;
        $this->filters = new FilterExpression([]);
        $this->parameters = new Parameters;
    }

    public function __clone()
    {
        $this->path = null;
        $this->source = null;
        $this->filters = clone $this->filters;
        $this->parameters = clone $this->parameters;
    }

    /**
     * getPath
     *
     * @return void
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * getSource
     *
     * @return void
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * setPath
     *
     * @param mixed $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * setSource
     *
     * @param mixed $source
     *
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * filter
     *
     * @param mixed $expr
     *
     * @return Generator.
     */
    public function filter($expr)
    {
        $this->filters->setExpression($expr);

        return $this;
    }

    /**
     * pixel
     *
     * @param mixed $px
     *
     * @return void
     */
    public function pixel($px)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RSIZEPXCOUNT);
        $this->parameters->setTargetSize($px);

        return $this->apply();
    }

    /**
     * scale
     *
     * @param mixed $perc
     *
     * @return string
     */
    public function scale($perc)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RSIZEPERCENT);
        $this->parameters->setTargetSize($perc);

        return $this->apply();
    }

    /**
     * fit
     *
     * @param mixed $width
     * @param mixed $height
     *
     * @return string
     */
    public function fit($width, $height)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RSIZEFIT);
        $this->parameters->setTargetSize($width, $height);

        return $this->apply();
    }

    /**
     * cropAndResize
     *
     * @param mixed $width
     * @param mixed $height
     * @param int $gravity
     *
     * @return string
     */
    public function cropAndResize($width, $height, $gravity = 5)
    {
        $this->parameters->setMode(ProcessorInterface::IM_SCALECROP);
        $this->parameters->setTargetSize($width, $height);
        $this->parameters->setGravity($gravity);

        return $this->apply();
    }

    /**
     * crop
     *
     * @param mixed $width
     * @param mixed $height
     * @param int $gravity
     * @param mixed $background
     *
     * @return string
     */
    public function crop($width, $height, $gravity = 5, $background = null)
    {
        $this->parameters->setMode(ProcessorInterface::IM_CROP);
        $this->parameters->setTargetSize($width, $height);
        $this->parameters->setGravity($gravity);
        $this->parameters->setBackground($background);

        return $this->apply();
    }

    /**
     * get
     *
     * @return string
     */
    public function get()
    {
        $this->parameters->setMode(ProcessorInterface::IM_NOSCALE);

        return $this->apply();
    }

    /**
     * resize
     *
     * @param mixed $width
     * @param mixed $height
     * @param int $gravity
     *
     * @return string
     */
    public function resize($width, $height, $gravity = 5)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RESIZE);
        $this->parameters->setTargetSize($width, $height);

        return $this->apply();
    }

    /**
     * apply
     *
     * @return string
     */
    protected function apply()
    {
        return $this->jmg->apply($this->path, $this->source, $this->parameters, $this->filters);
    }
}
