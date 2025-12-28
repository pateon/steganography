<?php

declare(strict_types=1);

namespace Steganography\Encoder;

use Steganography\Compressor\CompressorInterface;

class DefaultEncoder implements EncoderInterface
{
    public function encode(string $data, CompressorInterface $compressor): string
    {
        $compressed = base64_encode($compressor->compress($data));
        $bin = '';
        $length = strlen($compressed);

        for ($i = 0; $i < $length; $i++) {
            $bin .= sprintf('%08b', ord($compressed[$i]));
        }

        return $bin;
    }

    public function decode(string $data, CompressorInterface $compressor): string
    {
        $chars = str_split($data, 8);
        $compressed = '';

        foreach ($chars as $char) {
            $compressed .= chr(bindec($char));
        }

        return $compressor->decompress(base64_decode($compressed));
    }
}
