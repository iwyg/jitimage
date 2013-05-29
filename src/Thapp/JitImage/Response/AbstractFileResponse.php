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
 * Class: AbstractFileResponse
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
     * response
     *
     * @var mixed
     */
    protected $response;

    /**
     * make
     *
     * @param Image $image
     * @access public
     * @final
     * @return mixed
     */
    final public function make(Image $image)
    {
        $this->response = new Response(null, 200);
        $this->setHeaders($this->response, $image);
    }

    /**
     * send
     *
     * @access public
     * @return mixed
     */
    public function send()
    {
        if (!isset($this->response)) {
            throw new \Exception('response not created yet. Create a response before calling send.');
        }

        $this->response->send();
    }

    /**
     * setHeaders
     *
     * @param Response $response
     * @param Image $image
     * @access protected
     * @abstract
     * @return mixed
     */
    abstract protected function setHeaders(Response $response, Image $image);


    /**
     * abort
     *
     * @param int $status
     * @access public
     * @return mixed
     */
    public function abort($status = 404)
    {
        $response = new Response(null, $status);
        $response->send();
    }

    /**
     * notFound
     *
     * @access public
     * @return mixed
     */
    public function notFound()
    {
        throw new NotFoundHttpException;
    }

}
