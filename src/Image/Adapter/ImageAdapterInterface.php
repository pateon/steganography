<?php

declare(strict_types=1);

namespace Steganography\Image\Adapter;

use Steganography\Image\Color;

interface ImageAdapterInterface
{
    public function load(string $path): void;
    public function getWidth(): int;
    public function getHeight(): int;
    public function getColor(int $x, int $y): Color;
    public function setColor(int $x, int $y, Color $color): void;
    public function save(string $path): void;
    public function render(): string;
    public function destroy(): void;
}
