<?php

/**
 * This File is part of the Thapp\JitImage\Adapter package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Adapter;

use \Thapp\Image\Adapter\FlysystemLoader as FlyLoader;
use \GrahamCampbell\Flysystem\Managers\FlysystemManager;

/**
 * @class FlysystemLoader
 * @package Thapp\JitImage\Adapter
 * @version $Id$
 */
class FlysystemLoader extends FlyLoader
{
    private $manager;

    /**
     * @param FlysystemManager $manager
     *
     * @access public
     */
    public function __construct(FlysystemManager $manager)
    {
        $this->fs = $manager;
    }
}
