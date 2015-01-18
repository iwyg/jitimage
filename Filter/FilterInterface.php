<?php

/*
 * This File is part of the Thapp\JitImage package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Filter;

use Thapp\JitImage\ProcessorInterface;

/**
 * @interface FilterInterface
 *
 * @package Thapp\JitImage\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface FilterInterface
{
    /**
     * apply
     *
     * @param ProcessorInterface $proc
     *
     * @return void
     */
    public function apply(ProcessorInterface $proc, array $options = []);

    /**
     * supports
     *
     * @param ProcessorInterface $proc
     *
     * @return boolean
     */
    public function supports(ProcessorInterface $proc);
}
