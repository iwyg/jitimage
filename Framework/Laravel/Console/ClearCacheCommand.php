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

    /**
     * Constructor
     *
     * @param CacheClearer $clearer
     */
    public function __construct(CacheClearer $clearer)
    {
        parent::__construct();
        $this->clearer = $clearer;
    }

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $alias = $this->input->getOption('cache');
        $image = $this->input->getOption('image');

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
