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

use Thapp\JitImage\ImageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handle file response
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
    public function make(ImageInterface $image);

    /**
     * send the response
     *
     * @access public
     * @return void
     */
    public function send();

    /**
     * abort
     *
     * @deprecated will be removed with next version
     * @param int $status
     * @access public
     * @return void
     */
    public function abort($status = 404);

    /**
     * notFound
     *
     * @throws NotFoundHttpException;
     * @access public
     * @return void
     */
    public function notFound();
}
