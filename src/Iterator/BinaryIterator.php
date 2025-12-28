<?php

declare(strict_types=1);

namespace Steganography\Iterator;

use Iterator;

class BinaryIterator implements Iterator
{
    private string $string;
    private int $index = 0;
    private int $length;

    public function __construct(
        string $binaryString,
        private int $bitsPerPixel = 3
    ) {
        // Prepend 48-bit length header
        $this->string = sprintf('%048b', strlen($binaryString)) . $binaryString;
        $this->length = strlen($this->string);
    }

    public function current(): array
    {
        $part = substr($this->string, ($this->index * $this->bitsPerPixel), $this->bitsPerPixel);
        $chars = array_pad(str_split($part), $this->bitsPerPixel, '0');

        return [
            'r' => $chars[0],
            'g' => $chars[1],
            'b' => $chars[2],
        ];
    }

    public function next(): void
    {
        $this->index++;
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
