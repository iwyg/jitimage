<?php

/**
 * This File is part of the Thapp\Image package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Loader;

use Thapp\JitImage\Exception\SourceLoaderException;

/**
 * @class RemoteLoader extends AbstractLoader
 * @see AbstractLoader
 *
 * @package Thapp\Image
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class HttpLoader extends AbstractLoader
{
    /**
     * curl_error
     *
     * @var string
     */
    private $error;

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
    protected $source;

    /**
     * source
     *
     * @var array
     */
    protected $trustedHosts;

    /**
     * Create a new HttpLoader instance.
     */
    public function __construct(array $trustedHosts = [])
    {
        $this->trustedHosts = $trustedHosts;
        $this->tmp = sys_get_temp_dir();
    }

    /**
     * {@inheritdoc}
     *
     * @throws SourceLoaderException if fetching remote file fails.
     * @throws SourceLoaderException if the file is not an image.
     */
    public function load($url)
    {
        if (!$handle = $this->loadRemoteFile($url)) {
            throw new SourceLoaderException(
                sprintf('Error loading remote file "%s": %s', $url, $this->error ?: 'undefined error')
            );
        }

        if (!$resource = $this->validate($handle)) {
            throw new SourceLoaderException(sprintf('File "%s" is not an image.', $file));
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($url)
    {
        return is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https', 'spdy']);
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        parent::clean();

        $this->error = null;
    }

    /**
     * loadRemoteFile
     *
     * @param string $url
     * @return string|boolean false if error
     */
    private function loadRemoteFile($url)
    {
        if (!$this->isValidDomain($url)) {
            $this->error = sprintf('forbidden host `%s`', parse_url($url, PHP_URL_HOST));

            return false;
        }

        if (!function_exists('curl_init')) {
            if (false === $handle = @fopen($url, 'r')) {
                return $handle;
            }
        } else {
            $handle = tmpfile();

            if ($status = $this->fetchFile($handle, $url)) {
                return $handle;
            }

        }

        return false;
    }

    /**
     * fetchFile
     *
     * @param resource $handle
     * @param string $url
     * @return int the curl status
     */
    private function fetchFile(&$handle, $url)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FILE, $handle);

        $status = curl_exec($curl);
        $info = curl_getinfo($curl);

        if (!in_array($info['http_code'], [200, 302, 304])) {
            $this->error = 'resource not found';
            $status = false;
        }

        if (0 !== strlen($msg = curl_error($curl))) {
            $this->error = $msg;
            $status = false;
        }

        curl_close($curl);

        rewind($handle);

        return $status;
    }

    /**
     * isValidDomain
     *
     * @access protected
     * @return string|boolean
     */
    private function isValidDomain($url)
    {
        $trusted = $this->trustedHosts;

        if (!empty($trusted)) {

            $host = parse_url($url, PHP_URL_HOST);
            $host = substr($url, 0, strpos($url, $host)).$host;

            if (!$this->matchHost($host, $trusted)) {
                return false;
            }
        }

        return $url;
    }

    /**
     * matchHosts
     *
     * @param mixed $host
     * @param array $hosts
     *
     * @access protected
     * @return boolean
     */
    protected function matchHost($host, array $hosts)
    {
        foreach ($hosts as $trusted) {
            if (0 === strcmp($host, $trusted) || preg_match('#^'. $trusted .'#s', $host)) {
                return true;
            }
        }

        return false;
    }
}
