<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use Thapp\JitImage\Http\HttpSingerInterface;

/**
 * @class UrlBuilder
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlBuilder
{
    /**
     * Constructor.
     *
     * @param HttpSingerInterface $signer
     */
    public function __construct(HttpSingerInterface $signer = null)
    {
        $this->signer = $signer;
    }

    public function build(Parameters $params, FilterExpression $filters = null, $base = '')
    {
    }
}
