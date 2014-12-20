<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

/* ====================================================================
 * Processing driver paths
 * --------------------------------------------------------------------
 * Specify the processing driver, defaults to gd:
 *  - imagick: The imagick driver (http://php.net/manual/en/book.imagick.php).
 *  - gd:      The GD driver (http://php.net/manual/en/book.image.php).
 *  - gmagick: The gmagick driver (http://php.net/manual/en/book.gmagick.php).
 ====================================================================== */
$app['jmg.driver'] = 'imagick';

/* ====================================================================
 * Source paths
 ====================================================================== */
$app['jmg.paths'] = [
    // 'images' => $local_path . '/images'
    // 'url'    => ''
];

/* ====================================================================
 * Default paths
 ====================================================================== */
$app['jmg.default_path'] = '';

/* ====================================================================
 * Resource Loaders
 * --------------------------------------------------------------------
 * Define which loader should be delegated to load an image.
 *
 * Note that custom loaders must implement
 * `Thapp\JitImage\Loader\LoaderInterface`.
 * If your custom loader needs preparation, it must be registered on
 * the DIC beforehand.
 *
 * - file
 * - http
 * - dropbox
 * - aws3
 *
 ====================================================================== */
$app['jmg.loaders'] = [
    //'images' => 'file',
    //'url'    => 'http',
];

/* ====================================================================
 *  Disables dynamic processing vie uri.
 ====================================================================== */
$app['jmg.disable_dynamic_processing'] = true;

/* ====================================================================
 * Set mode constraints on scaling values
 * --------------------------------------------------------------------
 * Note that this will only affect on demand image processing.
 ====================================================================== */
$app['jmg.mode_constraints'] = [
    1   => [2000, 2000],  // max width and height 2000px
    2   => [2000, 2000],  // max width and height 2000px
    3   => [2000, 2000],  // max width and height 2000px
    4   => [2000, 2000],  // max width and height 2000px
    5   => [100],         // max scaling 100%
    6   => [4000000],     // max pixel count 4000000
];

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.recipes'] = [
    //'thumbs' => ['images', '2/200/200/5'],
    //'png' => ['images', '2/200/200/5, filter:conv;f=png']
];

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.default_cache'] = 'file';

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.default_cache_path'] = null; // set this to a local path

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.caches'] = [
    //'images' => 'file',
    //'url'   => 'file'
];

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.cache_prefix'] = 'jmg_';

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.cache_path_prefix'] = 'cached';

/* ====================================================================
 *
 *
 ====================================================================== */
$app['jmg.cache_connections'] = [];

/* ====================================================================
 * Set Domain constraints when loading images from remote http sources
 * ---------------------------------------------------------
 *
 * This will only affect the `jmg.loader.http` loader.
 *
 ====================================================================== */
$app['jmg.trusted_sites'] = [
    //'http://[0-9]+.media.tumblr.(com|de|net)',
];
