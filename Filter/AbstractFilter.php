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
    protected $availableOptions = [];

    /**
     * Get a filter option.
     *
     * @param string $option option name
     * @param mixed  $default the default value to return
     * @access public
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        return $default;
    }

    public function supports(ProcessorInterface $proc)
    {
        return true;
    }

    protected function setOptions(array $options)
    {
        $this->options = [];
        foreach ($options as $option => $value) {
            if (!in_array($option, (array)$this->availableOptions)) {
                throw new \InvalidArgumentException(
                    sprintf('filter %s has no option "%s"', get_class($this), $option)
                );
            }

            $this->options[$option] = $value;
        }
    }

    public function hexToRgb($hex)
    {
        if (3 === ($len = strlen($hex))) {
            $rgb = str_split($hex);
            list($r, $g, $b) = $rgb;
            $rgb = [hexdec($r.$r), hexdec($g.$g), hexdec($b.$b)];
        } elseif (6 === $len) {
            $rgb = str_split($hex, 2);
            list($r, $g, $b) = $rgb;
            $rgb = [hexdec($r), hexdec($g), hexdec($b)];
        } else {
            throw new \InvalidArgumentException(sprintf('invalid hex value %s', $hex));
        }
        return $rgb;
    }
}
