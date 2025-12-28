<?php

declare(strict_types=1);

namespace Steganography\Iterator;

use Iterator;

class RectIterator implements Iterator
{
    private int $index = 0;
    private int $x = 0;
    private int $y = 0;

    public function __construct(
        private int $width,
        private int $height
    ) {}

    public function current(): array
    {
        return [$this->x, $this->y];
    }

    public function next(): void
    {
        if ($this->x + 1 < $this->width) {
            $this->x++;
        } else {
            $this->x = 0;
            $this->y++;
        }

        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->x < $this->width && $this->y < $this->height;
    }

    public function rewind(): void
    {
        $this->index = 0;
        $this->x = 0;
        $this->y = 0;
    }
}
