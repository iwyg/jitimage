<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage;

use Thapp\JitImage\Resource\FileResourceInterface;

/**
 * @interface ImageInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface ProcessorInterface
{
    /**
     * @var int
     */
    const IM_NOSCALE      = 0;

    /**
     * @var int
     */
    const IM_RESIZE       = 1;

    /**
     * @var int
     */
    const IM_SCALECROP    = 2;

    /**
     * @var int
     */
    const IM_CROP         = 3;

    /**
     * @var int
     */
    const IM_RSIZEFIT     = 4;

    /**
     * @var int
     */
    const IM_RSIZEPERCENT = 5;

    /**
     * @var int
     */
    const IM_RSIZEPXCOUNT = 6;

    /**
     * @var string
     */
    const FORMAT_JPG = 'jpg';

    /**
     * @var string
     */
    const FORMAT_PNG = 'png';

    /**
     * @var string
     */
    const FORMAT_GIF = 'gif';

    /**
     * @var string
     */
    const FORMAT_TIF = 'tif';

    /**
     * setOptions
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options);

    /**
     * load the source file
     *
     * @param string $source source url
     *
     * @return boolean true on success or false on failure
     */
    public function load(FileResourceInterface $source);

    /**
     * close
     *
     * @return void
     */
    public function close();

    /**
     * process the image source give with an ImageResolver instance
     *
     * @param \Thapp\JitImage\ResolverInterface $resolver
     *
     * @return void
     */
    public function process(Parameters $parameters, FilteExpression $filters = null);

    /**
     * Get the image driver
     *
     * @return DriverInterface
     */
    public function getDriver();

    /**
     * set the image compression quality.
     *
     * This typically is a value between
     * 0 and 100
     *
     * @param int $quality
     *
     * @return void
     */
    //public function setQuality($quality);

    /**
     * set the output image format
     *
     * @param string $format
     *
     * @return void
     */
    public function setFileFormat($format);

    /**
     * get the image output format
     *
     * @return string
     */
    public function getFileFormat();

    /**
     * getSourceFormat
     *
     * @return string
     */
    public function getSourceFormat();

    /**
     * get the filecontents of the image
     *
     * @return string
     */
    public function getContents();

    /**
     * getSourceMimeTime
     *
     *
     * @return string
     */
    public function getSourceMimeType();

    /**
     * get the image output MimeType
     *
     * @return string
     */
    public function getMimeType();

    /**
     * get the image input source path
     *
     * @return string
     */
    public function getSource();

    /**
     * Determine if the image has been processed yet.
     *
     * @return bool
     */
    public function isProcessed();

    /**
     * getLastModTime
     *
     * @return integet
     */
    public function getLastModTime();

    /**
     * Get output dimensions in width and height
     *
     * @return mixed
     */
    public function getTargetSize();

    public function getCurrentImage();
}

