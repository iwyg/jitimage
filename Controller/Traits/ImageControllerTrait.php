<?php

/**
 * This File is part of the \Users\malcolm\www\image\src\Thapp\JitImage\Controller\Traits package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Controller\Traits;

/**
 * @class ImageControllerTrait
 * @package \Users\malcolm\www\image\src\Thapp\JitImage\Controller\Traits
 * @version $Id$
 */
trait ImageControllerTrait
{
    private $path;

    private $pathResolver;

    /**
     * getImage
     *
     * @param string $source
     * @param string $params
     * @param string $filter
     *
     * @return Response
     */
    public function getImage($alias, $params = null, $source = null, $filter = null, $base = null)
    {
        return null;
    }

    /**
     * getPathResolver
     *
     *
     * @access protected
     * @return ResolverInterface
     */
    protected function getPathResolver()
    {
        return $this->pathResolver;
    }

    /**
     * getPath
     *
     *
     * @access protected
     * @return string
     */
    protected function getPath()
    {
        return $this->path;
    }

    /**
     * getResponse
     *
     * @access protected
     * @return Resonse
     */
    protected function getResponse()
    {

    }
}
