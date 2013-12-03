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

        $response->setContent($content = $image->getContents());

        $response->setLastModified($lastMod);

        $response->setEtag(hash('md5', $response->getContent()));

        $response->headers->set('Accept-ranges', 'bytes');
        $response->headers->set('Keep-Alive', 'timeout=5, max=99');
        $response->headers->set('Connection', 'keep-alive', true);

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
