<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Driver;

use \Resource;
use Thapp\JitImage\Exception\ImageResourceLoaderException;

/**
 * Class: ImageSourceLoader
 *
 * @implements SourceLoaderInterface
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImageSourceLoader implements SourceLoaderInterface
{
    /**
     * tmp
     *
     * @var string
     */
    protected $tmp;

    /**
     * file
     *
     * @var string
     */
    protected $file;

    /**
     * src
     *
     * @var mixed
     */
    protected $source;

    /**
     * __construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->tmp = sys_get_temp_dir();
    }

    /**
     * load
     *
     * @param string $url file source url
     *
     * @access public
     * @throws \Thapp\Exception\ImageResourceLoaderException
     * @return string
     */
    public function load($url)
    {
        if (file_exists($url)) {
            return $this->validate($url);
        }

        if (preg_match('#^(https?|spdy)://#', $url)) {

            if ($file = $this->loadRemoteFile($url)) {
                return $this->validate($file);
            }
        }

        throw new ImageResourceLoaderException(sprintf('Invalid Source URL: %s', $url));
    }

    /**
     * valid
     *
     * @param mixed $url
     * @access private
     * @return mixed
     */
    private function validate($url)
    {
        if (@getimagesize($url)) {
            $this->source = $url;
            return $url;
        }

        return false;
    }

    public function getSource()
    {
        return $this->source;
    }

    /**
     * __destruct
     *
     * @access public
     */
    public function __destruct()
    {
        $this->clean();
    }

    /**
     * clean
     *
     * @access public
     * @return void
     */
    public function clean()
    {
        if (file_exists($this->file)) {
            @unlink($this->file);
        }
    }

    /**
     * loadRemoteFile
     *
     * @param mixed $url
     * @access protected
     * @return mixed
     */
    protected function loadRemoteFile($url)
    {
        $this->file = tempnam($this->tmp, 'jit_rmt_');

        if (!function_exists('curl_init')) {

            if (!$contents = file_get_contents($url)) {
                return false;
            }

            file_put_contents($contents, $this->file);

            return $this->file;

        }

        $handle = fopen($this->file, 'w');

        if (!$this->fetchFile($handle, $url)) {
            fclose($handle);
            return false;
        }

        fclose($handle);

        return $this->file;
    }

    /**
     * fetchFile
     *
     * @param Resource $handle
     * @access protected
     * @return void
     */
    protected function fetchFile($handle, $url, &$message = null)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FILE, $handle);

        $status = curl_exec($curl);
        $info = curl_getinfo($curl);

        if (!in_array($info['http_code'], [200, 302, 304])) {
            $status = false;
        }

        if (0 !== strlen($msg = curl_error($curl))) {
            $message = $msg;
            $status = false;
        }

        curl_close($curl);
        return $status;
    }
}
