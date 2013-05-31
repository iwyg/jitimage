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
 * X-Sendfile response handler
 *
 * @uses AbstractFileResponse
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class XsendFileResponse extends AbstractFileResponse
{
    /**
     * {@inheritdoc}
     */
    protected function setHeaders(Response $response, Image $image)
    {
        $this->image = $image;


        // return normal by setting image contents;
        if ($image->isProcessed()) {

            $response->setContent($image->getContents());
            $response->header('Content-type', $image->getMimeType());

            return;
        }

        // set the xsend header:
        $file = $image->getSource();

        $response->header('Content-type', $image->getMimeType());
        $response->header('Content-Disposition', sprintf('inline; filename="%s"', basename($file)));
        $response->header('X-Sendfile', $file);
    }

}
