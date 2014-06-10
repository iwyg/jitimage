<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | File Loaders
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'loaders' => [
        'Thapp\Image\Loader\FilesystemLoader',
        'Thapp\Image\Loader\RemoteLoader',
        'Thapp\JitImage\Adapter\FlysystemLoader',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing driver
    |--------------------------------------------------------------------------
    |
    | Specify the processing driver:
    |  - gd:      The GD driver (http://www.imagemagick.org/).
    |  - im:      The imagemagick driver (http://php.net/manual/en/book.image.php).
    |  - imagick: The imagick driver (http://php.net/manual/en/book.imagick.php).
    */

    'driver' => 'imagick',

    /*
    |--------------------------------------------------------------------------
    | On demand processing routes
    |--------------------------------------------------------------------------
    |
    | Define the base paths for images to be converted.
    | These paths do not neccessarily need to be public.
    |
    | The key will act as the base route.
    */

    'routes' => [
        'image'   => public_path() . '/test',
        'thumb'   => public_path() . '/thumbs',
        'foo/bar' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | On demand processing routes
    |--------------------------------------------------------------------------
    |
    | Define the base paths for images to be converted.
    | These paths do not neccessarily need to be public.
    |
    | The key will act as the base route.
    */
    'mode_constraints' => [
        1   => [2000, 2000],
        2   => [2000, 2000],
        3   => [2000, 2000],
        4   => [2000, 2000],
        5   => [150],
        6   => [100000],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'recipes' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'cache' => [

        //enable cache by default
        'enabled' => true,

        'path' => storage_path() . '/jitimage',

        // specify cache adapter for different routes
        'routes' => [
            'foo/bar' => [
                'class' => 'Thapp\JitImage\Adapter\FlysystemCache'
            ],
            'test' => [
                'enabled' => false
            ],
        ],
    ]
];
