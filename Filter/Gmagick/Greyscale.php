<?php

/*
 * This File is part of the Thapp\JitImage\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter\Gmagick;

use Thapp\JitImage\ProcessorInterface;

/**
 * @class Greyscale
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Greyscale extends AbstractImagickFilter
{
    protected $availableOptions = ['h', 's', 'b', 'c'];

    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        if (!$image = $proc->getCurrentImage()) {
            return;
        }

        $this->setOptions($options);
        $this->applyEffects($image->getGmagick());

    }

    protected function applyEffects(\Gmagick $img)
    {
        $img->modulateImage((int)$this->getOption('b', 100), (int)$this->getOption('s', 0), (int)$this->getOption('h', 100));

        if (method_exists($img, 'contrastImage')) {
            $img->contrastImage((bool)$this->getOption('c', 1));
        }
    }
}
