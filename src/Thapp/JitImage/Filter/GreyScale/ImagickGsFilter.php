<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter\GreyScale package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\GreyScale;

use Thapp\JitImage\Filter\ImagickFilter;

/**
 * @class ImagickGsFilter
 */

class ImagickGsFilter extends ImagickFilter
{
    /**
     * run
     *
     * @access public
     * @return void
     */
    public function run()
    {
        $this->driver->getResource()->modulateImage((int)$this->getOption('b', 100), (int)$this->getOption('s', 0), (int)$this->getOption('h', 100));
        $this->driver->getResource()->contrastImage((bool)$this->getOption('c', 1));
    }
}
