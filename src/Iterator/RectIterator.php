<?php

declare(strict_types=1);

namespace Steganography\Iterator;

use Iterator;

/**
 * Iterator that traverses a rectangular area pixel by pixel.
 * 
 * Iterates from (0,0) to (width-1, height-1) in row-major order,
 * yielding [x, y] coordinate pairs.
 * 
 * @implements Iterator<int, array{0: int, 1: int}>
 */
final class RectIterator implements Iterator
{
    private int $index = 0;
    private int $x = 0;
    private int $y = 0;

    public function __construct(
        private readonly int $width,
        private readonly int $height
    ) {}

    /**
     * @return array{0: int, 1: int}
     */
    public function current(): array
    {
        return [$this->x, $this->y];
    }

    public function next(): void
    {
        if (++$this->x >= $this->width) {
            $this->x = 0;
            ++$this->y;
        }
        ++$this->index;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->y < $this->height;
    }

    public function rewind(): void
    {
        $this->index = 0;
        $this->x = 0;
        $this->y = 0;
    }
}
