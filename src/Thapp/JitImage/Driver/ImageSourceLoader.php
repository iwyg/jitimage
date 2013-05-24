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
use Thapp\Exception\ImageResourceLoaderException;

/**
 * Class: ImageSourceLoader
 *
 * @implements SourceLoaderInterface
 *
 * @package
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
            return $url;
        }

        if (preg_match('#^(https?|spdy)://#', $url)) {
            if ($file = $this->loadRemoteFile($url)) {
                return $file;
            }
        }

        throw new ImageResourceLoaderException(sprintf('Invalid Source URL: %s', $url));
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
        $this->file =  tempnam($this->tmp, 'jit_rmt_');

        if (!function_exists('curl_init')) {

            file_put_contents(file_get_contents($url), $this->file);
            return $this->file;

        }

        $handle = fopen($this->file, 'w');

        $this->fetchFile($handle, $url);

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
    protected function fetchFile(\Resource $handle, $url)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_FILE, $handle);
        curl_exec($curl);
        curl_close($curl);
    }
}
