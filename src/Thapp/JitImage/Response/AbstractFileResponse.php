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

    public function __construct(array $etags = [])
    {
        $this->etags = $etags;
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
        $this->response = new Response(null, 200);
        $this->setHeaders($this->response, $image);
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

        if (in_array($this->response->getEtag(), $this->etags)) {
            $this->abort(304);
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
    abstract protected function setHeaders(Response $response, Image $image);


    /**
     * Abort with given status code.
     *
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
