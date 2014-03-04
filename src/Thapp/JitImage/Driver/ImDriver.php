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

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Thapp\JitImage\Shell\ShellCommand;
use Thapp\JitImage\Exception\ImageProcessException;

/**
 * Imagemagick Processing Driver
 *
 * @uses AbstractDriver
 *
 * @package Thapp\JitImage
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImDriver extends AbstractDriver
{
    use Scaling;

    use ShellCommand;

    /**
     * driverType
     *
     * @var string
     */
    protected static $driverType = 'im';

    /**
     * source
     *
     * @var string
     */
    protected $source;

    /**
     * loader
     *
     * @var mixed
     */
    protected $loader;

    /**
     * commands
     *
     * @var array
     */
    protected $commands = [];

    /**
     * a dictionary containing the intended width
     * and height values of the target file
     *
     * @var mixed
     */
    protected $targetSize = [];


    /**
     * path to temporary system directory
     *
     * @var string
     */
    protected $tmp;

    /**
     * temporary image file name
     *
     * @var string
     */
    protected $tmpFile;

    /**
     * intermediate
     *
     * @var mixed
     */
    protected $intermediate;

    /**
     * imageFrames
     *
     * @var int
     */
    protected $imageFrames;

    /**
     * path to convert binary
     *
     * @var string
     */
    private $converter;

    /**
     * __construct
     *
     * @access public
     * @return mixed
     */
    public function __construct(BinLocatorInterface $locator, SourceLoaderInterface $loader)
    {
        $this->tmp       = sys_get_temp_dir();
        $this->loader    = $loader;
        $this->converter = $locator->getConverterPath();
    }

    /**
     * {@inheritDoc}
     */
    public function load($source)
    {
        $this->clean();

        if ($src = $this->loader->load($source)) {
            $this->source = $src;
            return true;
        }

        $this->error = 'error loading source';
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Thapp\JitImage\Exception\ImageProcessException;
     */
    public function process()
    {
        parent::process();

        $cmd = $this->compile();
        $this->runCmd(
            $cmd,
            '\Thapp\JitImage\Exception\ImageProcessException',
            function ($stderr) {
                $this->clean();
            },
            ['#', PHP_EOL]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function clean()
    {
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }

        if (file_exists($this->intermediate)) {
            @unlink($this->intermediate);
        }

        $this->commands = [];
        $this->loader->clean();

        parent::clean();
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return boolean|void
     */
    public function filter($name, array $options = [])
    {
        if (static::EXT_FILTER === parent::filter($name, $options) and isset($this->filters[$name])) {
            $filter = new $this->filters[$name]($this, $options);

            $filterResults = $filter->run();

            if (!empty($filterResults)) {
                $this->commands = array_merge($this->commands, (array)$filterResults);
            }
        }
    }

    /**
     * getResource
     *
     * @access public
     * @return mixed
     */
    public function getResource()
    {
        return null;
    }

    /**
     * getResource
     *
     * @access public
     * @return mixed
     */
    public function swapResource($resource)
    {
        return null;
    }

    /**
     * setQuality
     *
     * @param mixed $quality
     * @access public
     * @return mixed
     */
    public function setQuality($quality)
    {
        $this->commands['-quality %d'] = [(int)$quality];
    }

    /**
     * {@inheritDoc}
     */
    public function getImageBlob()
    {
        
        return file_get_contents($this->tmpFile ?: $this->source);
        
    }

    /**
     * background
     *
     * @param string $color
     * @access protected
     * @return \Thapp\JitImage\Driver\ImagickDriver
     */
    protected function background($color = null)
    {
        if (is_string($color)) {
            $this->commands['-background "#%s"'] = [trim($color, '#')];
        }
        return $this;
    }

    /**
     * resize
     *
     * @access protected
     * @return \Thapp\JitImage\Driver\ImDriver
     */
    protected function resize($width, $height, $flag = '')
    {
        $min = min($width, $height);
        $cmd = '-resize %sx%s%s';

        switch ($flag) {
            case static::FL_OSRK_LGR:
                break;
            case static::FL_RESZ_PERC:
                $cmd = '-resize %s%s%s';
                break;
            case static::FL_IGNR_ASPR:
            default:
                // compensating some imagick /im differences:
                if (0 === $width) {
                    $width = (int)floor($height * $this->getInfo('ratio'));
                }
                if (0 === $height) {
                    $height = (int)floor($width / $this->getInfo('ratio'));
                }
                $h = '';
                break;
        }

        $w = $this->getValueString($width);
        $h = $this->getValueString($height);

        $this->commands[$cmd] = [$w, $h, $flag];
        return $this;
    }

    /**
     * getMinTargetSize
     *
     * @param mixed $w
     * @param mixed $h
     * @access private
     * @return mixed
     */
    private function getMinTargetSize($w, $h)
    {
        extract($this->getInfo());

        if ($w > $width or $h > $height) {
            $w = $width;
            $h = $height;
        }

        if ($w > $h) {
            extract($this->getFilesize($w, 0));
        } else {
            extract($this->getFilesize(0, $h));
        }

        $this->targetSize = compact('width', 'height');
    }

    /**
     * extent
     *
     * @param mixed $width
     * @param mixed $height
     * @param string $flag
     * @access protected
     * @return \Thapp\JitImage\Driver\ImDriver
     */
    protected function extent($width, $height, $flag = '')
    {
        $this->commands['-extent %sx%s%s'] = [(string)$width, (string)$height, $flag];

        return $this;
    }

    /**
     * gravity
     *
     * @param mixed $gravity
     * @param string $flag
     * @access protected
     * @return \Thapp\JitImage\Driver\ImDriver
     */
    protected function gravity($gravity, $flag = '')
    {
        $this->commands['-gravity %s%s'] = [$this->getGravityValue($gravity), $flag];

        return $this;
    }

    /**
     * scale
     *
     * @param mixed $width
     * @param mixed $height
     * @param string $flag
     *
     * @access protected
     * @return \Thapp\JitImage\Driver\ImDriver
     */
    protected function scale($width, $height, $flag = '')
    {
        $this->commands['-scale %s%s%s'] = [$width, $height, $flag = ''];

        return $this;
    }

    /**
     * repage
     *
     * @access protected
     * @return \Thapp\JitImage\Driver\ImDriver
     */
    protected function repage()
    {
        $this->commands['%srepage'] = ['+'];

        return $this;
    }

    /**
     * getTempFile
     *
     * @access protected
     * @return string
     */
    protected function getTempFile($extenstion = null)
    {
        $extenstion = is_null($extenstion) ? '' : '.'.$extenstion;
        return tempnam($this->tmp, 'jitim_'.$extenstion);
    }

    /**
     * isMultipartImage
     *
     * @access protected
     * @return mixed
     */
    protected function isMultipartImage()
    {
        if (!is_int($this->imageFrames)) {

            $type = $this->getInfo('type');

            if ('image/gif' !== $type and 'image/png' !== $type) {

                $this->imageFrames = 1;

            } else {

                $identify = dirname($this->converter) . '/identify';
                $cmd = sprintf('%s -format %s %s', $identify, '%n', $this->source);
                $this->imageFrames = (int)$this->runCmd(
                    $cmd,
                    '\Thapp\JitImage\Exception\ImageProcessException',
                    function ($stderr) {
                        $this->clean();
                    },
                    ['#']
                );

            }
        }
        return $this->imageFrames > 1;
    }

    /**
     * compile the convert command
     *
     * @access protected
     * @return string the compiled command
     */
    private function compile()
    {
        $commands = array_keys($this->commands);
        $values = $this->getArrayValues(array_values($this->commands));

        $origSource = $this->source;

        $vs = '%s';
        $bin = $this->converter;
        $type = preg_replace('#^image/#', null, $this->getInfo('type'));

        $this->tmpFile = $this->getTempFile();

        if ($this->isMultipartImage()) {

            $this->intermediate = $this->getTempFile($type);
            $this->source = $this->intermediate;

        }

        array_unshift($values, sprintf('%s:%s', $type, escapeshellarg($this->source)));

        array_unshift($values, $bin);

        array_unshift($commands, $vs);
        array_unshift($commands, $vs);

        if ($this->isMultipartImage()) {

            array_unshift(
                $values,
                sprintf(
                    '%s %s:%s -coalesce %s %s',
                    $this->converter,
                    $type,
                    $origSource,
                    $this->intermediate,
                    PHP_EOL
                )
            );
            array_unshift($commands, $vs);
        }

        array_push($values, sprintf('%s:%s', $this->getOutputType(), $this->tmpFile));
        array_push($commands, $vs);

        $cmd = implode(' ', $commands);

        $this->source = $origSource;

        return vsprintf($cmd, $values);
    }

    /**
     * getArrayValues
     *
     * @param mixed $array
     * @access private
     * @return mixed
     */
    private function getArrayValues($array)
    {
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

        foreach ($it as $value) {
            $out[] = $value;
        }
        return $out;
    }

    /**
     * getGravityValue
     *
     * @param mixed $gravity
     * @access protected
     * @return string
     */
    protected function getGravityValue($gravity)
    {
        switch ($gravity) {
            case 1:
                return 'northwest';
            case 2:
                return 'north';
            case 3:
                return 'northeast';
            case 4:
                return 'west';
            case 5:
                return 'center';
            case 6:
                return 'east';
            case 7:
                return 'southwest';
            case 8:
                return 'south';
            case 9:
                return 'southeast';
            default:
                return 'center';
        }
    }

    /**
     * getValueString
     *
     * @param mixed $value
     * @access private
     * @return string
     */
    private function getValueString($value)
    {
        return (string)(0 === $value ? '' : $value);
    }
}
