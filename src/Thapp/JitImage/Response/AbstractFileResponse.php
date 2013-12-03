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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handle file response
 *
 * @implements FileResponseInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
abstract class AbstractFileResponse implements FileResponseInterface
{
    /**
     * headers
     *
     * @var mixed
     */
    protected $headers = [];

    /**
     * etags
     *
     * @var mixed
     */
    protected $etags = [];

    /**
     * response
     *
     * @var mixed
     */
    protected $response;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Creates a new response.
     *
     * @param Image $image
     * @access public
     * @final
     * @return void
     */
    final public function make(Image $image)
    {
        $this->response = new Response;
        $this->response->setPublic();

        $modDate = $image->getLastModTime();
        $mod = strtotime($this->request->headers->get('if-modified-since', time()));

        $lastMod = (new \DateTime)->setTimestamp($modDate);

        if (!$image->isProcessed() && $mod === $modDate) {
            $this->setHeadersIfNotProcessed($this->response, $image, $lastMod);
        } else {
            $this->setHeaders($this->response, $image, $lastMod);
        }
    }

    public function getResponse()
    {
        return $this->response;
    }


    /**
     * Send response.
     *
     * @access public
     * @return void
     */
    public function send()
    {
        if (!isset($this->response)) {
            throw new \Exception('response not created yet. Create a response before calling send.');
        }

        $this->response->send();
    }

    /**
     * Set response headers.
     *
     * @param Response $response
     * @param Image $image
     * @access protected
     * @abstract
     * @return void
     */
    abstract protected function setHeaders(Response $response, Image $image, \DateTime $lastMod);

    /**
     * setHeadersIfNotProcessed
     *
     * @param Response $response
     * @param Image $image
     * @param \DateTime $lastMod
     *
     * @access protected
     * @abstract
     * @return void
     */
    abstract protected function setHeadersIfNotProcessed(Response $response, Image $image, \DateTime $lastMod);

    /**
     * Abort with given status code.
     *
     * @deprecated will be removed with next version
     * @param int $status
     * @access public
     * @return void
     */
    public function abort($status = 404)
    {
        $response = new Response(null, $status);
        $response->send();
    }

    /**
     * Not found handler.
     *
     * @access public
     * @return void
     */
    public function notFound()
    {
        throw new NotFoundHttpException;
    }

    /**
     * __call
     *
     * @param mixed $method
     * @param mixed $arguments
     *
     * @access public
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array($this->response, $arguments);
    }
}
