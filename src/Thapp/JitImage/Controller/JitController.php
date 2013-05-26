<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Controller;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Thapp\JitImage\ImageInterface;
use Illuminate\Container\Container;
use Thapp\JitImage\ResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class: JitController
 *
 * @uses \BaseController
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitController extends \BaseController
{
    /**
     * image
     *
     * @var \Thapp\JitImage\ImageInterface
     */
    protected $image;

    /**
     * defaults
     *
     * @var mixed
     */
    protected $defaults;

    /**
     * defaults
     *
     * @var mixed
     */
    protected $response;

    /**
     * __construct
     *
     * @param Image $image
     * @access public
     */
    public function __construct(ResolverInterface $imageResolver)
    {
        $this->imageResolver = $imageResolver;
    }

    /**
     * getImage
     *
     * @param string $mode
     * @param string $height
     * @param string $width
     * @param string $gravity
     * @param string $options
     * @access public
     *
     * @return void
     */
    public function getImage($parameter, $source, $filter = null)
    {
        $this->imageResolver->setParameter($parameter);
        $this->imageResolver->setSource($source);
        $this->imageResolver->setFilter($filter);

        if ($image = $this->imageResolver->resolve()) {
            $this->render($image);
        }

        return $this->notFound();
    }

    /**
     * getResource
     *
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function getResource($source)
    {
        extract($this->defaults);

        if (is_string($parameter) and strlen($parameter) > 0) {

            list($parameters, $filter) = array_pad(
                explode(',', str_replace(' ', null, $parameter)),
                2, null
            );

            return $this->getImage($parameters, $source, $filter);
        }

        return $this->notFound();
    }

    /**
     * callAction
     *
     * @access public
     * @return void
     */
    public function callAction(Container $container, Router $router, $method, $parameters)
    {
        $this->defaults = $router->getCurrentRoute()->getDefaults();
        parent::callAction($container, $router, $method, $parameters);
    }

    /**
     * getCached
     *
     * @param mixed $source
     * @access public
     * @return mixed
     */
    public function getCached($id)
    {
        if ($image = $this->imageResolver->resolveFromCache($id)) {
            return $this->render($image);
        }

        return $this->notFound();
    }

    /**
     * notFound
     *
     * @access protected
     * @return mixed
     */
    protected function notFound()
    {
        $this->imageResolver->close();
        throw new NotFoundHttpException;
    }


    /**
     * render
     *
     * @access protected
     * @return mixed
     */
    protected function render(ImageInterface $image)
    {
        $response = new Response($image->getContents(), 200);
        $response->header('Content-type', $image->getMimeType());

        $image->close();
        $this->imageResolver->close();

        $response->send();
    }
}
