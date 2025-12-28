<?php

declare(strict_types=1);

namespace Steganography\Encoder;

use Steganography\Compressor\CompressorInterface;

/**
 * Default encoder that compresses, base64-encodes, and converts data to binary.
 * 
 * The encoding pipeline:
 * 1. Compress the input data
 * 2. Base64 encode the compressed data
 * 3. Convert each character to 8-bit binary representation
 */
final class DefaultEncoder implements EncoderInterface
{
    public function encode(string $data, CompressorInterface $compressor): string
    {
        $compressed = base64_encode($compressor->compress($data));
        $binary = '';
        $length = strlen($compressed);

        for ($i = 0; $i < $length; ++$i) {
            $binary .= sprintf('%08b', ord($compressed[$i]));
        }

        return $binary;
    }

    public function decode(string $data, CompressorInterface $compressor): string
    {
        $chars = str_split($data, 8);
        $compressed = '';

        foreach ($chars as $char) {
            $compressed .= chr((int) bindec($char));
        }

        $decoded = base64_decode($compressed, strict: true);
        
        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 data');
        }

        return $compressor->decompress($decoded);
    }
}
