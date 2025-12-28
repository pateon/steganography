<?php

declare(strict_types=1);

namespace Steganography\Exception;

/**
 * Exception thrown when decoding a hidden message fails.
 */
final class DecodeException extends SteganographyException
{
    public function __construct(string $message = 'Failed to decode message from image')
    {
        parent::__construct($message);
    }
}
