<?php

declare(strict_types=1);

namespace Steganography\Compressor;

use RuntimeException;

/**
 * Zlib-based compressor implementation.
 * 
 * Uses gzcompress/gzuncompress for data compression
 * with configurable compression level.
 */
final class ZlibCompressor implements CompressorInterface
{
    /**
     * @param int $level Compression level (-1 for default, 0-9 for specific levels)
     */
    public function __construct(
        private readonly int $level = -1
    ) {}

    public function compress(string $data): string
    {
        $compressed = gzcompress($data, $this->level);
        
        if ($compressed === false) {
            throw new RuntimeException('Compression failed');
        }
        
        return $compressed;
    }

    public function decompress(string $data): string
    {
        $decompressed = gzuncompress($data);
        
        if ($decompressed === false) {
            throw new RuntimeException('Decompression failed');
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
