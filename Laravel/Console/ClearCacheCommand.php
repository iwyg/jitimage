<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Laravel\Console;

use \Illuminate\Console\Command;
use \Thapp\Image\Cache\CacheInterface;
use \Thapp\JitImage\Resolver\ResolverInterface;
use \Symfony\Component\Console\Input\InputOption;

/**
 * @class ClearCacheCommand
 * @package Thapp\JitImage
 * @version $Id$
 */
class ClearCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jitimage:clearcache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear JitImage cache.';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var \Thapp\JitImage\Resolver\ResolverInterface
     */
    private $resolver;

    /**
     * Create a new command instance.
     */
    public function __construct(ResolverInterface $cacheResolver)
    {
        parent::__construct();
        $this->resolver = $cacheResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $alias = $this->input->getOption('cache');

        if ($image = $this->input->getOption('image')) {
            return $this->deleteImage($image, $alias);
        }

        if ($cache = $this->resolver->resolve($alias)) {
            $this->clearCache($cache, $alias);

            return;
        }

        foreach ($this->resolver as $alias => $cache) {
            $this->clearCache($cache, $alias);
        }
    }

    /**
     * @param string $image
     * @param string $alias
     *
     * @return void
     */
    private function deleteImage($image, $alias = null)
    {
        $cache = $this->resolver->resolve($alias);

        if (null !== $cache) {
            $this->deleteImageCache($cache, $image, $alias);
            return;
        }

        foreach ($this->resolver as $alias => $cache) {
            $this->deleteImageCache($cache, $image, $alias);
        }
    }

    /**
     * @param CacheInterface $cache
     * @param string $image
     * @param string $alias
     *
     * @return void
     */
    private function deleteImageCache(CacheInterface $cache, $image, $alias = 'cache')
    {
        if ($cache->delete($image)) {
            $this->info($image . ' cleared');
        }

        $this->info($alias . ': nothing to delete');
    }

    /**
     * @param CacheInterface $cache
     * @param string $alias
     *
     * @return void
     */
    private function clearCache(CacheInterface $cache, $alias)
    {
        $this->info('clear cache for '. $alias);

        try {
            if (false !== $cache->purge()) {
                return true;
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [
            ['cache', null, InputOption::VALUE_OPTIONAL, 'The aliasd cache name', null],
            ['image', null, InputOption::VALUE_OPTIONAL, 'The image group to delete from the cache.', null]
        ];
    }
}
