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
    const IM_NOSCALE      = 0;
    const IM_RESIZE       = 1;
    const IM_SCALECROP    = 2;
    const IM_CROP         = 3;
    const IM_RSIZEFIT     = 4;
    const IM_RSIZEPERCENT = 5;
    const IM_RSIZEPXCOUNT = 6;

    const FORMAT_JPG = 'jpg';
    const FORMAT_PNG = 'png';
    const FORMAT_GIF = 'gif';
    const FORMAT_TIF = 'tif';

    /**
     * Set output options.
     *
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options);

    /**
     * Set an output option.
     *
     * @param string $option
     * @param mixed $value
     *
     * @return void
     */
    public function setOption($option, $value);

    /**
     * Load the source file.
     *
     * @param string $source source url
     *
     * @return boolean true on success or false on failure
     */
    public function load(FileResourceInterface $source);

    /**
     * Close the processor
     *
     * Clears resource and drivers.
     *
     * @return void
     */
    public function close();

    /**
     * Process the image source give with an ImageResolver instance.
     *
     * @param \Thapp\JitImage\ResolverInterface $resolver
     *
     * @return void
     */
    public function process(Parameters $parameters, FilterExpression $filters = null);

    /**
     * Get the image driver
     *
     * @return DriverInterface
     */
    public function getDriver();

    /**
     * Set the output image format
     *
     * @param string $format
     *
     * @return void
     */
    public function setFileFormat($format);

    /**
     * Get the image output format
     *
     * @return string
     */
    public function getFileFormat();

    /**
     * Get the image output extension
     *
     * @return string
     */
    public function getFileExtension();

    /**
     * Get the original image format.
     *
     * @return string
     */
    public function getSourceFormat();

    /**
     * Get the image contents.
     *
     * @return string
     */
    public function getContents();

    /**
     * Get the mimetype of the input image.
     *
     * @return string
     */
    public function getSourceMimeType();

    /**
     * Get the image output MimeType.
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Get the image input source path.
     *
     * @return string
     */
    public function getSource();

    /**
     * Determine if the image has been processed yet.
     *
     * @return boolean
     */
    public function isProcessed();

    /**
     * Get the last modification time of the image.
     *
     * @return integer
     */
    public function getLastModTime();

    /**
     * Get output dimensions in width and height
     *
     * @return array int[$width, $height].
     */
    public function getTargetSize();
}

