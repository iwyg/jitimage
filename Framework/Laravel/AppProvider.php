<?php

/*
 * This File is part of the Thapp\JitImage\Framework\Laravel package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Framework\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * @class AppProvider
 *
 * @package Thapp\JitImage\Framework\Laravel
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AppProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    final public function register()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function when()
    {
        return ['jmg.processor.boot'];
    }
}
