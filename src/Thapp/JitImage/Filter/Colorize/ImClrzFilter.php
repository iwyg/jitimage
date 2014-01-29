<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Colorize;

use Thapp\JitImage\Filter\ImFilter;

/**
 * @class ImOvlyFilter
 * @package vendor\thapp\jitimage\src\Thapp\JitImage\Filter\Overlay
 * @version $Id$
 */
class ImClrzFilter extends ImFilter
{
    protected $availableOptions = ['c'];

    public function run()
    {
        return ['( +clone -fill rgb(%s) -colorize 100 ) -compose Colorize -composite' => [
                implode(',', $this->hexToRgb($this->getOption('c')))
            ]
        ];
    }
}
