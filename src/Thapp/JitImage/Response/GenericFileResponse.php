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
use Illuminate\Http\Response;

/**
 * Generic response handler
 *
 * @uses AbstractFileResponse
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class GenericFileResponse extends AbstractFileResponse
{

    /**
     * {@inheritdoc}
     */
    protected function setHeaders(Response $response, Image $image)
    {
        $response->setContent($image->getContents());
        $response->header('Content-type', $image->getMimeType());
    }
}
