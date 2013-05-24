<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Filter package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

use Thapp\JitImage\Driver\DriverInterface;

/**
 * Class: FilterInterface
 *
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface FilterInterface
{
    public function __construct(DriverInterface $driver, $options);

    public function run();
}
