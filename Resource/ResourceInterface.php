<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

/**
 * @interface ResourceInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface ResourceInterface
{
    /**
     * isLocal
     *
     * @return boolean
     */
    public function isLocal();

    /**
     * isFresh
     *
     * @return boolean
     */
    public function isFresh();

    /**
     * getContents
     *
     * @return string
     */
    public function getContents();

    /**
     * getMimeType
     *
     * @return string
     */
    public function getMimeType();

    /**
     * getPath
     *
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getFileName();
}
