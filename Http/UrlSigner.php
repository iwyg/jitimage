<?php

/*
 * This File is part of the Thapp\JitImage\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Http;

use Thapp\JitImage\Parameters;
use Thapp\JitImage\FilterExpression;
use Thapp\JitImage\Exception\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @class UrlSigner
 *
 * @package Thapp\JitImage\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlSigner implements HttpSignerInterface
{
    private $key;
    private $qkey;

    /**
     * Constructor.
     *
     * @param string $key
     * @param qkey $key
     */
    public function __construct($key, $qkey = 'token')
    {
        $this->key  = $key;
        $this->qkey = $qkey;
    }

    /**
     * {@inheritdoc}
     */
    public function sign($path, Parameters $params, FilterExpression $filters = null)
    {
        return $path.'?'.http_build_query([$this->qkey => $this->createSignature($path, $params, $filters)]);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Request $request, Parameters $params, FilterExpression $filters = null)
    {
        if (null === $token = $request->query->get($this->qkey)) {
            throw InvalidSignatureException::missingSignature();
        }

        if (0 !== strcmp($token, $this->createSignature($request->getPathInfo(), $params, $filters))) {
            throw InvalidSignatureException::invalidSignature();
        }

        return true;
    }

    /**
     * createSignature
     *
     * @param mixed $path
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return string
     */
    protected function createSignature($path, Parameters $params, FilterExpression $filters = null)
    {
        $filterStr = null !== $filters && 0 < count($filters->all()) ? (string)$filters : '';

        return hash(
            'md5',
            sprintf('%s:%s%sfilter:%s', $this->key, trim($path, '/'), (string)$params, $filterStr)
        );
    }
}
