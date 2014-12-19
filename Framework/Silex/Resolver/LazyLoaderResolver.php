<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Silex\Resolver;

use Silex\Application;
use Thapp\JitImage\Resolver\LoaderResolver;
use Thapp\JitImage\Framework\Common\Resolver\LazyResolverTrait;

/**
 * @class LazyLoaderResolver
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class LazyLoaderResolver extends LoaderResolver
{
    use LazyResolverTrait;

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($prefix)
    {
        if (null !== $loader = parent::resolve($prefix = trim($prefix, '/'))) {
            return $loader;
        }

        if (null === $loader = $this->resolveLoader($prefix)) {
            return;
        }

        $this->add($prefix, $loader);

        return $loader;
    }

    /**
     * resolveLoader
     *
     * @param string $prefix
     *
     * @return Thapp\JitImage\Loader\LoaderInterface `NULL` if none is found.
     */
    protected function resolveLoader($prefix)
    {
        if (null === $name = $this->getLoaderName($prefix)) {
            return;
        }

        if (method_exists($this, $method = 'create'.ucfirst($name).'Loader')) {
            return call_user_func([$this, $method]);
        } elseif ($this->hasCustomCreator($name)) {
            return $this->callCustomCreator($name, [$this->app]);
        }
    }

    /**
     * getLoaderName
     *
     * @param string $prefix
     *
     * @return string
     */
    protected function getLoaderName($prefix)
    {
        if (isset($this->app['jmg.loaders'][$prefix])) {
            return $this->app['jmg.loaders'][$prefix];
        }
    }

    /**
     * createFileLoader
     *
     * @return void
     */
    protected function createFileLoader()
    {
        return new \Thapp\JitImage\Loader\FilesystemLoader;
    }

    /**
     * createHttpLoader
     *
     * @return void
     */
    protected function createHttpLoader()
    {
        $config = isset($this->app['jmg.trusted_sites']) ? $this->app['jmg.trusted_sites'] : [];

        return new \Thapp\JitImage\Loader\HttpLoader($config);
    }

    /**
     * createHttpLoader
     *
     * @return void
     */
    protected function createDropboxLoader()
    {
        throw new \LogicException('Not implemented yet.');

        return new \Thapp\JitImage\Loader\DropboxLoader();
    }
}
