<?php

/*
 * This File is part of the Thapp\JitImage\Image\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter;

use Thapp\JitImage\ProcessorInterface;
use Thapp\JitImage\Filter\ModulateFilterTrait as ModulateFilterHelper;

/**
 * @trait ModulateFilterTrait
 *
 * @package Thapp\JitImage\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ModulateFilterTrait
{
    use ModulateFilterHelper;

    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);
        $bri = $this->getOption('b', 100.0);
        $sat = $this->getOption('s', 100.0);
        $hue = $this->getOption('h', 100.0);

        $proc->getDriver()->filter($this->newModulate($bri, $sat, $hue));
    }

    abstract protected function newModulate($bri, $sat, $hue);
}
