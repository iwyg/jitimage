<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


/**
 * Class: JitImageCacheClearCommand
 *
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class JitImageCacheClearCommand extends Command
{
    /**
     * config
     *
     * @var array
     */
    protected $config;

    /**
     * container
     *
     * @var Illuminate\Container\Container
     */
    protected $files;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        try {
            foreach ($this->files->files(storage_path() . DIRECTORY_SEPARATOR . 'jit') as $file) {
                unlink($file);
            }
        } catch (\Exception $e) {}

        $this->info('cache was successfully cleared');
    }
}
