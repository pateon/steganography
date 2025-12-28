<?php

declare(strict_types=1);

namespace Steganography\Encoder;

use Steganography\Compressor\CompressorInterface;

interface EncoderInterface
{
    public function encode(string $data, CompressorInterface $compressor): string;
    public function decode(string $data, CompressorInterface $compressor): string;
}
