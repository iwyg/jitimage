<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Http;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;

/**
 * @interface UrlBuilderInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface UrlBuilderInterface
{
    /**
     * getUri
     *
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $prefix
     *
     * @return string
     */
    public function getUri($source, Parameters $params, FilterExpression $filters = null, $prefix = '');
}
