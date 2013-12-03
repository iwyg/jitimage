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
    private $xsend;
    /**
     * {@inheritdoc}
     */
    protected function setHeaders(Response $response, Image $image, \DateTime $lastMod)
    {
        $response->headers->set('Content-type', $image->getMimeType());

        $response->setLastModified($lastMod);


        $response->headers->set('Accept-ranges', 'bytes');
        $response->headers->set('Keep-Alive', 'timeout=5, max=99');
        $response->headers->set('Connection', 'keep-alive', true);
        $response->setEtag(hash('md5', $content = $response->getContent()));

        // return normal by setting image contents;
        if ($image->isProcessed()) {
            $this->xsend = false;
            $response->setContent($content);
        } else {

            // set the xsend header:
            $this->xsend = true;

            $file = $image->getSource();
            $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', basename($file)));
            $response->headers->set('X-Sendfile', $file);
        }
    }

    public function send()
    {
        if ($this->xsend) {
            $this->response->sendHeaders();
            exit(0);
        }
        $this->response->send();
    }
}
