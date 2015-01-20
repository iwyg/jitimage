<?php

/*
 * This File is part of the Thapp\JitImage\Exception package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\JitImage\Exception;

use InvalidArgumentException;

/**
 * @class InvalidSignatureException
 *
 * @package Thapp\JitImage\Exception
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class InvalidSignatureException extends InvalidArgumentException
{
    public static function missingSignature()
    {
        return new self('Signature is missing.');
    }

    public static function invalidSignature()
    {
        return new self('Signature is invalid.');
    }
}
