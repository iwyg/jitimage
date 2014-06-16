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
    | On demand processing routes
    |--------------------------------------------------------------------------
    |
    | Define the base paths for images to be converted.
    | These paths do not neccessarily need to be public.
    |
    | The key will act as the base path of the image uri.
    */
    'paths' => [
        'image'   => public_path() . '/test',
        'thumb'   => public_path() . '/thumbs',
        'porn'    => public_path() . '/thumbs',
        'dropbox' => 'assets',
    ],

    /*
    |--------------------------------------------------------------------------
    | The default path when ommittin JitImage::from()
    |--------------------------------------------------------------------------
    */
    'default_path' => 'image',

    /*
    |--------------------------------------------------------------------------
    | Disable dynamic route processing
    |--------------------------------------------------------------------------
    |
    | Set this to true, if you want to dispable all dynamic processing routes
    */
    'disable_dynamic_processing' => false,

    /*
    |--------------------------------------------------------------------------
    | Set mode constraints on scaling values
    |--------------------------------------------------------------------------
    |
    | Note that this will only affect on demand image processing.
    */
    'mode_constraints' => [
        1   => [2000, 2000],  // max width and height 2000px
        2   => [2000, 2000],  // max width and height 2000px
        3   => [2000, 2000],  // max width and height 2000px
        4   => [2000, 2000],  // max width and height 2000px
        5   => [100],         // max scaling 100%
        6   => [4000000],     // max pixel count 4000000
    ],

    /*
    |--------------------------------------------------------------------------
    | Predefined processing assignments
    |--------------------------------------------------------------------------
    |
    | Use this predefined processing instructions.
    | The parent key must correspond to an existing image path alias.
    | You can set as many prosessing aliases per path alias as you like
    */
    'recipes' => [
        'image' => [
            'gallery' => '1/800/0',
            'thumbs'  => '2/200/200/5',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    */
    'cache' => [

        // enable cache by default
        'enabled' => true,

        // the default first uri segment
        // 'default' => 'image',

        // the default path suffix
        'suffix' => 'cached',

        // the default storage path, used for the default Filesystemloader
        //'path' => storage_path() . '/jitimage',
        'path' => public_path() . '/cache',

        // specify cache adapter for different routes
        // Note: custom adapters must implement Thapp\Image\Cache\CacheInterface
        'paths' => [
            'image' => [
                'enabled' => true
            ],
            'dropbox' => [
                'service' => 'Thapp\JitImage\Adapter\FlysystemCache'
            ],
            'thumb' => [
                'enabled' => false
            ],
            'gallery' => [
                'path' => public_path() . '/cache'
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing driver
    |--------------------------------------------------------------------------
    |
    | Specify the processing driver, defaults to gd:
    |  - imagick: The imagick driver (http://php.net/manual/en/book.imagick.php).
    |  - gd:      The GD driver (http://www.imagemagick.org/).
    |  - im:      The imagemagick driver (http://php.net/manual/en/book.image.php).
    */
    'driver' => 'imagick',

    /*
    |--------------------------------------------------------------------------
    | File Loaders
    |--------------------------------------------------------------------------
    |
    | Define which loader should be delegated to load an image.
    | Note that custom loaders must implement \Thapp\Image\Loader\LoaderInterface.
    | If your custom loader needs preparation, it must be registered on
    | the DIC beforehand
    |
    */
    'loaders' => [
        'Thapp\Image\Loader\FilesystemLoader',
        'Thapp\Image\Loader\RemoteLoader',
        'Thapp\JitImage\Adapter\FlysystemLoader',
    ],

    /*
    |--------------------------------------------------------------------------
    | Set Domain constraints when loading images from remote http sources
    |--------------------------------------------------------------------------
    |
    | This will only affect the `\Thapp\Image\Loader\RemoteLoader` class.
    */
    'trusted_sites' => [
        //'http://[0-9]+.media.tumblr.(com|de|net)',
    ]
];
