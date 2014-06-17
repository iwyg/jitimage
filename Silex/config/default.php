<?php

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
$app['jitimage.paths'] = [
    //'image'   => __DIR__ . '/../web/images',
    //'thumb'   => __DIR__ . '/../web/thumbs',
    //'porn'    => __DIR__ . '/../web/thumbs',
    //'dropbox' => 'assets',
];

/*
|--------------------------------------------------------------------------
| The default path when ommittin JitImage::from()
|--------------------------------------------------------------------------
*/
$app['jitimage.default_path'] = 'image';

/*
|--------------------------------------------------------------------------
| Disable dynamic route processing
|--------------------------------------------------------------------------
|
| Set this to true, if you want to dispable all dynamic processing routes
*/
$app['jitimage.disable_dynamic_processing'] = false;

/*
|--------------------------------------------------------------------------
| Set mode constraints on scaling values
|--------------------------------------------------------------------------
|
| Note that this will only affect on demand image processing.
*/
$app['jitimage.mode_constraints'] = [
    1   => [2000, 2000],  // max width and height 2000px
    2   => [2000, 2000],  // max width and height 2000px
    3   => [2000, 2000],  // max width and height 2000px
    4   => [2000, 2000],  // max width and height 2000px
    5   => [100],         // max scaling 100%
    6   => [4000000],     // max pixel count 4000000
];

/*
|--------------------------------------------------------------------------
| Predefined processing assignments
|--------------------------------------------------------------------------
|
| Use this predefined processing instructions.
| The parent key must correspond to an existing image path alias.
| You can set as many prosessing aliases per path alias as you like
*/
$app['jitimage.recipes'] = [
    //'image' => [
    //    'gallery' => '1/800/0',
    //    'thumbs'  => '2/200/200/5',
    //]
];

/*
|--------------------------------------------------------------------------
| Cache
|--------------------------------------------------------------------------
|
*/
// enable cache by default
$app['jitimage.cache.enabled'] = true;

// the default path suffix
$app['jitimage.cache.suffix']  = 'cached';

// the default storage path, used for the default Filesystemloader
//$app['jitimage.cache.path']    = __DIR__ . '/../web/cache';

// specify cache adapter for different routes
// Note: custom adapters must implement Thapp\Image\Cache\CacheInterface
$app['jitimage.cache.paths'] = [
    //'image' => [
    //    'enabled' => true
    //],
    //'dropbox' => [
    //    'service' => 'Thapp\JitImage\Adapter\FlysystemCache'
    //],
    //'thumb' => [
    //    'enabled' => false
    //],
    //'gallery' => [
    //    'path' => __DIR__ . '/cache'
    //],
];

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
$app['jitimage.driver'] = 'imagick';

/*
|--------------------------------------------------------------------------
| Image Compression quality
|--------------------------------------------------------------------------
| Higher means better image quality but also larger file size.
*/
$app['jitimage.quality'] = 60;

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
$app['jitimage.loaders'] = [
    'jitimage.filesystem_loader',
    'jitimage.remote_loader',
];

/*
|--------------------------------------------------------------------------
| Set Domain constraints when loading images from remote http sources
|--------------------------------------------------------------------------
|
| This will only affect the `\Thapp\Image\Loader\RemoteLoader` class.
*/
$app['jitimage.trusted_sites'] = [
    //'http://[0-9]+.media.tumblr.(com|de|net)',
];
