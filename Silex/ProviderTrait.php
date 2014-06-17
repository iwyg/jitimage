<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Silex;

use \Silex\Application;

/**
 * @trait SilexProviderTrait
 * @package Thapp\JitImage
 * @version $Id$
 */
trait ProviderTrait
{
    private $app;

    private function getDefault(Application $app, $key, $default = null)
    {
        return isset($app[$key]) ?$app[$key]  : $default;
    }

    private function get($key, $default = null)
    {
        return $this->getDefault($this->app, $key, $default);
    }
}
