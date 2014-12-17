<?php

/*
 * This File is part of the Thapp\JitImage\Loader package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Loader;

/**
 * @interface LoaderInterface
 *
 * @package Thapp\JitImage\Loader
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface LoaderInterface
{
    /**
     * load
     *
     * @param string|resource $source
     *
     * @return resource A file handle
     */
    public function load($source);

    public function clean();

    /**
     * supports
     *
     * @param mixed $resource
     *
     * @return boolean
     */
    public function supports($resource);
}
