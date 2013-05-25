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

use Illuminate\Routing\Router;
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

        if (!$resolved = $this->imageResolver->resolve()) {
            throw new NotFoundHttpException;
        }

        return $this->render($this->imageResolver->getImage());
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

        if ($parameter) {
            $params = list($parameters, $filter) = array_pad(explode(',', $parameter), 2, null);
            return $this->getImage($parameters, $source, $filter);
        }
        die;
    }

    /**
     * render
     *
     * @access protected
     * @return mixed
     */
    protected function render($image)
    {
        header('Content-type: ' .  $image->getMimeType());
        echo $image->getContents();
        $image->close();
        exit(0);
    }

    /**
     * callAction
     *
     * @access public
     * @return mixed
     */
    public function callAction(Container $container, Router $router, $method, $parameters)
    {

        $this->defaults = $router->getCurrentRoute()->getDefaults();
        //extract($defaults);
        //var_dump($alias);
        //var_dump($defaults); die;


        //if (true !== in_array($alias, array_keys($defaults['alias']))) {
            //return $this->handleMissing();
        //}

        parent::callAction($container, $router, $method, $parameters);
    }
}
