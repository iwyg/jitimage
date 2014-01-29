<?php

/**
 * This File is part of the Thapp\JitImage\Filter\Convert package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Convert;

use \Thapp\JitImage\Filter\ImagickFilter;

/**
 * @class ImagickConvFilter
 * @package Thapp\JitImage\Filter\Convert
 * @version $Id$
 */
class ImagickConvFilter extends ImagickFilter
{
    protected $availableOptions = ['f'];

    public function run()
    {
        $type = $this->getOption('f', 'jpg');
        $this->driver->setOutputType($type);
    }
}
