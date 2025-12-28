<?php

declare(strict_types=1);

namespace Steganography\Compressor;

interface CompressorInterface
{
    public function compress(string $data): string;
    public function decompress(string $data): string;
    public function isSupported(): bool;
    public function getName(): string;
}
