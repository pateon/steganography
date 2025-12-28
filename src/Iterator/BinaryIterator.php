<?php

declare(strict_types=1);

namespace Steganography\Iterator;

use Iterator;

/**
 * Iterator for binary string data, yielding RGB bit groups.
 * 
 * Prepends a 48-bit length header and yields groups of bits
 * for embedding into RGB channels.
 * 
 * @implements Iterator<int, array{r: string, g: string, b: string}>
 */
final class BinaryIterator implements Iterator
{
    private readonly string $string;
    private int $index = 0;
    private readonly int $length;

    public function __construct(
        string $binaryString,
        private readonly int $bitsPerPixel = 3
    ) {
        // Prepend 48-bit length header
        $this->string = sprintf('%048b', strlen($binaryString)) . $binaryString;
        $this->length = strlen($this->string);
    }

    /**
     * @return array{r: string, g: string, b: string}
     */
    public function current(): array
    {
        $part = substr($this->string, $this->index * $this->bitsPerPixel, $this->bitsPerPixel);
        $chars = str_split($part);
        
        return [
            'r' => $chars[0] ?? '0',
            'g' => $chars[1] ?? '0',
            'b' => $chars[2] ?? '0',
        ];
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->index * $this->bitsPerPixel < $this->length;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}
