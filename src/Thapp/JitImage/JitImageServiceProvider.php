<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use Illuminate\Support\ServiceProvider;

/**
 * Class: JitImageServiceProvider
 *
 * @uses ServiceProvider
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageServiceProvider extends ServiceProvider
{
    /**
     * register
     *
     * @access public
     * @return void
     */
    public function register()
    {
        $this->package('thapp/jitimage');
        $this->registerDriver();
        $this->registerController();

    }

    /**
     * registerDriver
     *
     * @access protected
     * @return void
     */
    protected function registerDriver()
    {
        $driver = sprintf('\Thapp\JitImage\Driver\%sDriver', ucfirst($this->app['config']->get('jitimage:driver', 'gd')));
        $this->app->register('Thapp\JitImage\Driver\DriverInterface', $driver);
    }
    /**
     * registerController
     *
     * @access protected
     * @return mixed
     */
    protected function registerController()
    {
        $this->app['router']->controller('image/{$mode}/{$height}/{$width}/{$gravity}', 'Thapp\JitImage\Controller\JitController');
    }
}
