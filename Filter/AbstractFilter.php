<?php

/*
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

use Thapp\JitImage\ProcessorInterface;

/**
 * @class AbstractFilter
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractFilter implements FilterInterface
{
    protected $options = [];

    /**
     * {@inheritdoc}
     */
    public function supports(ProcessorInterface $proc)
    {
        return true;
    }

    /**
     * setOptions
     *
     * @param array $options
     *
     * @return void
     */
    protected function setOptions(array $options)
    {
        $this->options = [];
        foreach ($options as $option => $value) {
            if (null === ($option = $this->translateOption($option))) {
                throw new \InvalidArgumentException(
                    sprintf('filter %s has no option "%s"', get_class($this), $option)
                );
            }

            $this->options[$option] = $this->parseOption($option, $value);
        }
    }

    /**
     * parseOption
     *
     * @param string $option
     * @param mixed $value
     *
     * @return mixed
     */
    protected function parseOption($option, $value)
    {
        return $value;
    }

    /**
     * Set a filter option.
     *
     * @param string $option option name
     * @param mixed  $default the default value to return
     * @access public
     * @return mixed
     */
    protected function getOption($option, $default = null)
    {
        if (!$option = $this->translateOption($option)) {
            return;
        }

        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        return $default;
    }

    /**
     * translateOption
     *
     * @param string $option
     *
     * @return string
     */
    protected function translateOption($option)
    {
        $shortOpts = $this->getShortOpts();
        if (isset($shortOpts[$option])) {
            return $option;
        } elseif (in_array($option, $shortOpts)) {
            $opts = array_flip($shortOpts);

            return $opts[$option];
        }
    }

    protected function getShortOpts()
    {
        return [];
    }
}
