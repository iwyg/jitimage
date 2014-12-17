<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Loader;

/**
 * @class DelegatingLoader
 * @see LoaderInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class DelegatingLoader implements LoaderInterface
{
    /**
     * loader
     *
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * loaders
     *
     * @var array
     */
    protected $loaders;

    /**
     * @param array $loaders
     *
     * @access public
     */
    public function __construct(array $loaders = [])
    {
        $this->setLoaders($loaders);
    }

    /**
     * load
     *
     * @param string $resource
     *
     * @return string
     */
    public function load($resource)
    {
        $loader = $this->getLoader($resource);

        return $loader->load($resource);
    }

    /**
     * clean
     *
     *
     * @access public
     * @return void
     */
    public function clean()
    {
        foreach ($this->loaders as $loader) {
            $loader->clean();
        }

        $this->loader = null;
    }

    /**
     * supports
     *
     * @param mixed $file
     *
     * @access public
     * @return boolean
     */
    public function supports($file)
    {
        $this->clean();

        foreach ($this->loaders as $loader) {
            if ($loader->supports($file)) {
                $this->loader = $loader;

                return true;
            }
        }

        return false;
    }

    public function __clone()
    {
        $this->loader = null;

        $loaders = [];

        foreach ($this->loaders as $loader) {
            $loaders[] = clone($loader);
        }

        $this->loaders = $loaders;
    }

    /**
     * clean
     *
     *
     * @access public
     * @return mixed
     */
    public function getSource()
    {
        if ($this->loader) {
            return $this->loader->getSource();
        }
    }

    /**
     * addLoader
     *
     * @param LoaderInterface $loader
     *
     * @access public
     * @return void
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * setLoaders
     *
     * @param array $loaders
     *
     * @access public
     * @return void
     */
    public function setLoaders(array $loaders)
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * getLoader
     *
     * @param mixed $file
     *
     * @access protected
     * @return mixed
     */
    protected function getLoader($file)
    {
        if (!$this->supports($file)) {
            throw new \InvalidArgumentException(sprintf('No suitable loader found for resource "%s"', $file));
        }

        return $this->loader;
    }
}
