<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Resource;

use \Thapp\Image\ProcessorInterface;
use \Thapp\Image\Cache\Client\ClientInterface;

/**
 * @class CachedResource extends AbstractResource
 * @see AbstractResource
 *
 * @package Thapp\Image
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class CacheClientResource extends CachedResource
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var ClientInterface
     */
    private $client;

    public function isLocal()
    {
        return false;
    }

    /**
     * setId
     *
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * setClient
     *
     * @param \Memcached $client
     *
     * @return void
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * initContent
     *
     * @return \Closure
     */
    protected function initContent()
    {
        return function () {
            return $this->client->get($this->id);
        };
    }
}
