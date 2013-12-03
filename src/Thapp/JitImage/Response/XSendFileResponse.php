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
//use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response;

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
class XsendFileResponse extends GenericFileResponse
{
    /**
     * {@inheritdoc}
     */
    protected function setHeaders(Response $response, Image $image, \DateTime $lastMod)
    {
        $response->headers->set('Content-type', $image->getMimeType());

        $response->headers->set('max-age', 600, true);
        $response->setContent($content = $image->getContents());
        $response->headers->set('Content-Length', strlen($content));

        $response->setLastModified($lastMod);

        $response->setEtag(hash('md5', $response->getContent()));

        // return normal by setting image contents;
        if ($image->isProcessed()) {
            $response->setContent($image->getContents());
            $response->setEtag(hash('md5', $response->getContent()));
            return;
        }

        // set the xsend header:
        $file = $image->getSource();

        $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', basename($file)));
        $response->headers->set('X-Sendfile', $file);
    }
}
