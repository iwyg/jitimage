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

/**
 * Interface: ImageInterface
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
interface ImageInterface
{
    /**
     * load the source file
     *
     * @param string $source source url
     *
     * @access public
     * @return boolean true on success or false on failure
     */
    public function load($source);

    /**
     * process the image source give with an ImageResolver instance
     *
     * @param \Thapp\JitImage\ResolverInterface $resolver
     * @access public
     * @return void
     */
    public function process(ResolverInterface $resolver);

    /**
     * set the image compression quality.
     *
     * This typically is a value between
     * 0 and 100
     *
     * @param int $quality
     *
     * @access public
     * @return void
     */
    public function setQuality($quality);

    /**
     * set the output image format
     *
     * @param string $format
     *
     * @access public
     * @return void
     */
    public function setFileFormat($format);

    /**
     * get the filecontents of the image
     *
     * @access public
     * @return string
     */
    public function getContents();

    /**
     * get the image output format
     *
     * @access public
     * @return string
     */
    public function getFileFormat();

    /**
     * getSourceFormat
     *
     * @access public
     * @return string
     */
    public function getSourceFormat();

    /**
     * getSourceMimeTime
     *
     *
     * @access public
     * @return string
     */
    public function getSourceMimeTime();

    /**
     * get the image output MimeType
     *
     * @access public
     * @return string
     */
    public function getMimeType();

    /**
     * get the image input source path
     *
     * @access public
     * @return string
     */
    public function getSource();

    /**
     * Determine if the image has been processed yet.
     *
     * @access public
     * @return bool
     */
    public function isProcessed();

    /**
     * getLastModTime
     *
     * @access public
     * @return integet
     */
    public function getLastModTime();

    /**
     * close
     *
     * @access public
     * @return void
     */
    public function close();
}
