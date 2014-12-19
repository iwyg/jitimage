<?php

/**
 * This File is part of the Thapp\JitImage package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Twig;

use \Thapp\JitImage\JitImage;

/**
 * @class JitImageExtension
 * @package Thapp\JitImage
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class JmgExtension extends \Twig_Extension
{
    /**
     * image
     *
     * @var JitImage
     */
    private $image;

    /**
     * from
     *
     * @var string
     */
    private $from;

    /**
     * Creates a new Twig Extension
     *
     * @param JitImage $image
     */
    public function __construct(Jmg $jmg)
    {
        $this->jmg = $jmg;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'jmg';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('jmg', function ($image, $path = null) {
                return $this->jmg->take($image, $path);
            })
        ];
    }
}
