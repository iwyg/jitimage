<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\GreyScale;

use Thapp\JitImage\Filter\ImagickFilter;

/**
 * Class: ImagickGsFilter
 *
 * @uses ImagickFilter
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImagickGsFilter extends ImagickFilter
{

    protected $availableOptions = ['h', 's', 'b', 'c'];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->driver->getResource()->modulateImage((int)$this->getOption('b', 100), (int)$this->getOption('s', 0), (int)$this->getOption('h', 100));
        $this->driver->getResource()->contrastImage((bool)$this->getOption('c', 1));
    }
}
