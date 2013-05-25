<?php

/**
 * This File is part of the vendor\thapp\jitimage\src\Thapp\JitImage\Driver package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Driver;

use Thapp\JitImage\Shell\ShellCommand;
use Thapp\JitImage\Exception\ImageProcessException;

/**
 * Class: ImDriver
 *
 * @uses AbstractDriver
 *
 * @package
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
    public function __construct(BinLocatorInterface $locator)
    {
        $this->tmp       = sys_get_temp_dir();
        $this->converter = $locator->getConverterPath();
    }


    /**
     * {@inheritDoc}
     */
    public function load($source)
    {
        $this->source = $source;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Thapp\JitImage\Exception\ImageProcessException;
     */
    public function process()
    {
        $cmd = $this->compile();

        $this->runCmd($cmd, '\Thapp\JitImage\Exception\ImageProcessException',
            function ($stderr)
            {
                $this->clean();
            }
        );
    }

    /**
     * clean
     *
     * @access public
     * @return void
     */
    public function clean()
    {
        if (file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
    }

    /**
     * filter
     *
     * @param mixed $name
     * @param mixed $options
     * @access public
     * @return boolean|void
     */
    public function filter($name, $options)
    {
        if (static::EXT_FILTER === parent::filter($name, $options)) {

            $filter = new $this->filters[$name]($this, $options);
            $this->commands = array_merge($this->commands, $filter->run());

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
        if ($this->tmpFile) {
            return file_get_contents($this->tmpFile);
        }
        return file_get_contents($this->source);
    }



    /**
     * background
     *
     * @param mixed $color
     * @access protected
     * @return \Thapp\JitImage\Driver\ImagickDriver
     */
    protected function background($color = null)
    {
        if (!is_null($color)) {
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
        // compensating some imagick /im differences:
        if (0 === $width) {
            $width = (int)floor($height * $this->getInfo('ratio'));
        }
        if (0 === $height) {
            $height = (int)floor($width / $this->getInfo('ratio'));
        }

        $w = $this->getValueString($width);
        $h = $this->getValueString($height);

        switch ($flag) {
        case static::FL_IGNR_ASPR:
            // if one value is zero, imagmagick will
            // replace that value with the max. image size instead of the
            // scaled valiue. so removeing the ignore ascpect ration flag will
            // fix it
            if (0 === min($height, $width)) {
                $flag = '';

            }
        case static::FL_OSRK_LGR:
            break;
        default:
            $h = '';
            break;
        }

        $this->commands['-resize %sx%s%s'] = [$w, $h, $flag];
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
            $w = $width; $h = $height;
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
    protected function getTempFile()
    {
        return tempnam($this->tmp, 'jitim_');
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
        $values = array_flatten(array_values($this->commands));

        $vs = '%s';
        $bin = $this->converter;

        $this->tmpFile = $this->getTempFile();

        array_unshift($values, $this->source);
        array_unshift($values, $bin);

        array_unshift($commands, $vs);
        array_unshift($commands, $vs);

        array_push($values, sprintf('%s:%s', $this->getOutputType(), $this->tmpFile));
        array_push($commands, $vs);

        $cmd = implode(' ', $commands);

        return vsprintf($cmd, $values);
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
        return (string)($value === 0 ? '' : $value);
    }

}
