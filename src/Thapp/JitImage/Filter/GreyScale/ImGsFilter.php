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

use Thapp\JitImage\Filter\ImFilter;

/**
 * Class: ImagickGsFilter
 *
 * @uses ImagickFilter
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImGsFilter extends ImFilter
{
    /**
     * run
     *
     * @access public
     * @return void
     */
    public function run()
    {
        return [
            '-modulate %s,%s,%s' => [(int)$this->getOption('b', 100), (int)$this->getOption('s', 0), (int)$this->getOption('h', 100)],
            '%scontrast' => [false !== (bool)$this->getOption('c', 1) ? '-' : '+'],
        ];
    }
}
