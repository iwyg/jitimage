<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Loader;

use Thapp\JitImage\Resource\FileResource;
use Thapp\JitImage\Resource\FileResourceInterface;

/**
 * @abstract class AbstractLoader implements LoaderInterface
 * @see LoaderInterface
 * @abstract
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
abstract class AbstractLoader implements LoaderInterface
{
    /**
     * source
     *
     * @var FileResourceInterface
     */
    protected $source;

    /**
     * getSource
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Clean up un object removal
     *
     * @return void
     */
    public function __destruct()
    {
        $this->clean();
    }

    /**
     * Clone the loader instance.
     *
     * @return void
     */
    public function __clone()
    {
        $this->source = null;
    }

    /**
     * clean
     *
     * @return void
     */
    public function clean()
    {
        if ($this->source instanceof FileResourceInterface && is_resource($handle = $this->source->getHandle())) {
            fclose($handle);
        }

        $this->source = null;
    }

    /**
     * valid
     *
     * @param string $url
     * @return boolean
     */
    protected function validate(&$resource)
    {
        $content = fread($resource, 8);
        rewind($resource);

        $info = finfo_open(FILEINFO_MIME);

        list($mime, ) = array_pad(explode(';', finfo_buffer($info, $content), 2), 2, null);
        finfo_close($info);

        if (0 === strpos($mime, 'image')) {
            return $this->source = new FileResource($resource, $mime);
        }

        return false;
    }
}
