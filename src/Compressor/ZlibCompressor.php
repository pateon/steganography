<?php

declare(strict_types=1);

namespace Steganography\Compressor;

class ZlibCompressor implements CompressorInterface
{
    public function __construct(
        private int $level = -1
    ) {}

    public function compress(string $data): string
    {
        $compressed = gzcompress($data, $this->level);
        if ($compressed === false) {
            throw new \RuntimeException("Compression failed");
        }
        return $compressed;
    }

    public function decompress(string $data): string
    {
        $decompressed = gzuncompress($data);
        if ($decompressed === false) {
            throw new \RuntimeException("Decompression failed");
        }
        return $decompressed;
    }

    public function isSupported(): bool
    {
        return function_exists('gzcompress');
    }

    public function getName(): string
    {
        return 'zlib';
    }
}
