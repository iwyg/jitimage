<?php

/*
 * This File is part of the Thapp\JitImage\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Http;

use \Illuminate\Routing\Controller as BaseController;

/**
 * @class Controller
 *
 * @package Thapp\JitImage\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Controller extends BaseController
{
    public function getImage($params = null, $source = null, $filter = null)
    {
        var_dump($params);
        die;
        return 'there be images';
    }
}
