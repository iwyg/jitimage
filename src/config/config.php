<?php

return [

    /* ------------------------------------------------
     * the base route:
     * ------------------------------------------------
     */

    'route'      => 'images',


    /* ------------------------------------------------
     * the base directory from where to resolve images
     * ------------------------------------------------
     */

    'base'       => public_path(),

    /* ------------------------------------------------
     * driver that powers the image manipulation:
     *
     * `gd` (the GDlibraray), `im` (the Imagemagick library),
     *  or `imagick` (this Imagick php extension).
     * ------------------------------------------------
     */

    'driver'      => 'im',

    /* ------------------------------------------------
     * cache processed images only in this environments
     * ------------------------------------------------
     */

    'cache'       => ['production'],

    /* ------------------------------------------------
     * the compression quality:
     * 0 - 100
     * ------------------------------------------------
     */

    'quality'     => 60,

    /* ------------------------------------------------
     * imagemagick specific settings:
     * ------------------------------------------------
     */

    'imagemagick' => [
        'path' => '/usr/local/bin',
        'bin'  => 'convert',
    ],

    /* ------------------------------------------------
     * image filter:
     * ------------------------------------------------
     */

    'filter' => [
        'Circle'    => 'circ',
        'GreyScale' => 'gs'
    ],

    /* ------------------------------------------------
     * only this expression should be allowed
     *
     * allow mode 2 crop rescale, with a 200x200 px crop and a grey scale
     * filter:
     *
     *  '2/200/200/5/,filter:gs'
     *
     * allow mode 1 resize, with a resize of 800px width
     * and any filter filter:
     *
     *  '1/400/0/,filter:circ',
     *
     * allow mode 5 best fit, with a resize of max 800px width
     * and 600px height, no filters:
     *
     *  '5/800/600'
     *
     * ------------------------------------------------
     */

    'recepies' => [
        //'thumbs' => '2/500/500/5/,filter:circ:gs'
    ],

    /* ------------------------------------------------
     * a list of trusted sites that deliver assets:
     *  e.g. 'media.tumblr.com'
     * ------------------------------------------------
     */
    'trusted-sites' => [],
];

