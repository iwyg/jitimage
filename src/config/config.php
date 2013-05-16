<?php

return [
    /* ------------------------------------------------
     * driver that powers the image manipulation:
     *
     * `gd` (the GDlibraray), `im` (the Imagemagick library),
     *  or `imagick` (this Imagick php extension).
     * ------------------------------------------------
     */

    'driver'      => 'gd',

    /* ------------------------------------------------
     * the outputquality of the processed image:
     * 0 - 100
     * ------------------------------------------------
     */

    'quality'     => '80',

    /* ------------------------------------------------
     * imagemagick specific settings:
     * ------------------------------------------------
     */

    'imagemagick' => [
        'path' => '/usr/local/bin'
    ]
];
