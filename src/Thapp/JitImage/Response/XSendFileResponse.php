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
    protected function setHeaders(Response $response, ImageInterface $image, \DateTime $lastMod)
    {
        $response->headers->set('Content-type', $image->getMimeType());

        $response->setLastModified($lastMod);

        $response->headers->set('Accept-ranges', 'bytes');
        $response->headers->set('Keep-Alive', 'timeout=5, max=99');
        $response->headers->set('Connection', 'keep-alive', true);

        // return normal by setting image contents;
        if ($image->isProcessed()) {
            $response->setContent($content = $image->getContents());
            $response->setEtag(hash('md5', $content));
        } else {

            // set the xsend header:
            $file = $image->getSource();

            $response->setEtag(md5_file($file));
            $response->headers->set('Content-Length', filesize($file));
            $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', basename($file)));
            $response->headers->set('X-Sendfile', $file);
        }
    }

    public function send()
    {
        $this->response->send();
    }
}
