<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Cache;

/**
 * @class FileHelper
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
trait FileHelper
{
    /**
     * dumpFile
     *
     * @param string $file
     * @param string $contents
     *
     * @return boolean
     */
    protected function dumpFile($file, $contents)
    {
        $this->ensureDir($dir = dirname($file));

        if (!is_file($file)) {
            if (false === @touch($file)) {
                return false;
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'hlpr');

        file_put_contents($tmp, $contents);

        $source = fopen($tmp, 'r');
        $target = fopen($file, 'w');

        $result = stream_copy_to_stream($source, $target);

        fclose($source);
        fclose($target);

        unlink($tmp);

        return $result;
    }

    /**
     * ensureDir
     *
     * @param string $path
     *
     * @return void
     */
    protected function ensureDir($path)
    {
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
    }

    /**
     * deleteDir
     *
     * @param string $dir
     *
     * @return boolean
     */
    protected function deleteDir($dir)
    {
        if (!$this->sweepDir($dir)) {
            return false;
        }

        if (false !== @rmdir($dir)) {
            return !$this->isDir($dir);
        }

        return false;
    }

    /**
     * recursiveDelete
     *
     * @param string $dir
     *
     * @return boolean
     */
    protected function sweepDir($dir)
    {
        if (!$this->isDir($dir)) {
            return false;
        }

        foreach (new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS) as $path => $item) {

            if ($item->isFile()) {
                unlink($item);
                continue;
            }

            if ($item->isDir()) {
                $this->deleteDir($path);
            }
        }

        return $this->isDir($dir);
    }

    /**
     * isFile
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isFile($path)
    {
        return is_file($path) && stream_is_local($path);
    }

    /**
     * isDir
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isDir($path)
    {
        return is_dir($path) && stream_is_local($path);
    }

    /**
     * exists
     *
     * @param string $file
     *
     * @return boolean
     */
    public function exists($file)
    {
        return $this->isDir($file) || $this->isFile($file);
    }
}
