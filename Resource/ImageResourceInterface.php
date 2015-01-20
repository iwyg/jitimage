<?php

/*
 * This File is part of the Thapp\JitImage\Resource package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

/**
 * @interface ImageResourceInterface
 *
 * @package Thapp\JitImage\Resource
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface ImageResourceInterface
{
    public function getWidth();

    public function getHeight();
}
