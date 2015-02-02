<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel\Console;

use Illuminate\Console\Command;
use Thapp\JitImage\Cache\CacheClearer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearCacheCommand extends Command
{
    private $clearer;

    protected $name = 'jmg:clearcache';

    protected $description = 'Clear JitImage cache.';

    public function __construct(CacheClearer $clearer)
    {
        parent::__construct();
        $this->clearer = $clearer;
    }

    public function fire()
    {
        $alias = $this->input->getOption('cache');
        $image = $this->input->getOption('image');

        var_dump($alias);

        if (null !== $image) {
            if ($this->clearer->clearImage($image, $alias)) {
                $this->info('Cache for "'.$image . '" cleared.');
            } else {
                $this->error('Cache for "'. $image . '" could not be cleared.');
            }

            return;
        }

        if ($this->clearer->clear($alias)) {
            if (null !== $alias) {
                $this->info('Cache "'.$alias . '" cleared.');
            } else {
                $this->info('All Caches cleared');
            }

        } else {
            $this->error('Cache "'. $alias . '" could not be cleared.');
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
