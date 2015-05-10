<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Image\Filter\Gmagick;

use Thapp\JitImage\ProcessorInterface;
use Thapp\Image\Filter\Gmagick\Grayscale as GmageGrayscale;

/**
 * @class Grayscale
 *
 * @package Thapp\JitImage
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Grayscale extends AbstractGmagickFilter
{
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $proc->getDriver()->filter(new GmageGrayscale);
    }
}
