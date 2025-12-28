<?php

namespace Steganography\Exception;

class DecodeException extends SteganographyException
{
    public function __construct(string $message = 'Failed to decode message from image')
    {
        parent::__construct($message);
    }
}
