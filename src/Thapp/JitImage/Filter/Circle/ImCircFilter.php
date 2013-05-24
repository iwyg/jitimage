<?php

/**
 * This File is part of the Thapp\JitImage\Filter\Oval package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Circle;

use Thapp\JitImage\Filter\ImFilter;

/**
 * Class: ImOvFilter
 *
 * @uses ImFilter
 *
 * @package
 * @version
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImCircFilter extends ImFilter
{

    /**
     * run
     *
     * @access public
     * @return mixed
     */
    public function run()
    {
        $this->driver->setOutPutType('png');

        extract($this->driver->getTargetSize());

        return [
            '( +clone -threshold -1 -negate -fill white -draw "circle %s,%s %s,%s" -gamma 2.2 ) -alpha Off -compose CopyOpacity -composite' =>
                $this->getCoordinates($width, $height)
        ];
    }

    /**
     * getCoordinates
     *
     * @param mixed $width
     * @param mixed $height
     * @access protected
     * @return mixed
     */
    protected function getCoordinates($width, $height)
    {
        $max = (int)ceil(max($width, $height) / 2);
        $min = (int)ceil(min($width, $height) / 2);

        return $width > $height ?
            [$max, $min, $max, $this->getOption('o', 1)]:
            [$min, $max, $this->getOption('o', 1), $max];
    }
}
