<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Response;

use Thapp\JitImage\Image;

/**
 * Interface: FileResponseInterface
 *
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface FileResponseInterface
{
    /**
     * create a new response
     *
     * @param  \Thapp\JitImage\Image $file the file object
     * @access public
     * @return void
     */
    public function make(Image $image);

    /**
     * send the response
     *
     * @access public
     * @return send
     */
    public function send();
}
