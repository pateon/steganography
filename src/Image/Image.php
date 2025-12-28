<?php

declare(strict_types=1);

namespace Steganography\Image;

use Steganography\Image\Adapter\ImageAdapterInterface;
use Steganography\Iterator\BinaryIterator;
use Steganography\Iterator\RectIterator;
use MultipleIterator;
use LimitIterator;

/**
 * Image wrapper that handles LSB steganography encoding/decoding.
 * 
 * Provides methods to embed binary data into image pixels using
 * the least significant bits of RGB channels.
 */
final class Image
{
    public function __construct(
        private readonly ImageAdapterInterface $adapter
    ) {}

    /**
     * Embed binary data into image pixels using LSB steganography.
     */
    public function setBinaryString(BinaryIterator $binary): void
    {
        $iterator = new MultipleIterator(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_ASSOC);
        $iterator->attachIterator(new RectIterator($this->adapter->getWidth(), $this->adapter->getHeight()), 'rect');
        $iterator->attachIterator($binary, 'bin');

        foreach ($iterator as $current) {
            [$x, $y] = $current['rect'];
            $bits = $current['bin']; // ['r' => bit, 'g' => bit, 'b' => bit]
            
            $this->setPixelLsb($x, $y, $bits);
        }
    }

    /**
     * Extract binary data from image pixels using LSB steganography.
     */
    public function getBinaryString(int $bitsPerPixel, int $lengthBits): string
    {
        $iterator = new RectIterator($this->adapter->getWidth(), $this->adapter->getHeight());
        $lengthBin = '';
        $dataBin = '';
        
        // Read length header
        $offset = (int) ($lengthBits / $bitsPerPixel);
        
        foreach (new LimitIterator($iterator, 0, $offset) as $pos) {
            $lengthBin .= $this->getPixelLsb($pos[0], $pos[1]);
        }

        $bits = (int) bindec($lengthBin);
        $length = (int) ceil($bits / $bitsPerPixel);

        // Read data
        foreach (new LimitIterator($iterator, $offset, $length) as $pos) {
            $dataBin .= $this->getPixelLsb($pos[0], $pos[1]);
        }

        return substr($dataBin, 0, $bits);
    }

    /**
     * Modify the LSB of R, G, B channels for a single pixel.
     * 
     * @param array<string, string> $bits Array with 'r', 'g', 'b' keys containing '0' or '1'
     */
    private function setPixelLsb(int $x, int $y, array $bits): void
    {
        $color = $this->adapter->getColor($x, $y);
        
        // Use bitwise operations for efficiency (clear LSB then set)
        $color->r = ($color->r & 0xFE) | (int) $bits['r'];
        $color->g = ($color->g & 0xFE) | (int) $bits['g'];
        $color->b = ($color->b & 0xFE) | (int) $bits['b'];

        $this->adapter->setColor($x, $y, $color);
    }

    /**
     * Extract the LSB from R, G, B channels of a single pixel.
     */
    private function getPixelLsb(int $x, int $y): string
    {
        $color = $this->adapter->getColor($x, $y);
        
        // Use bitwise AND for efficiency
        return ($color->r & 1) . ($color->g & 1) . ($color->b & 1);
    }

    public function save(string $path): void
    {
        $this->adapter->save($path);
    }
    
    public function render(): string
    {
        return $this->adapter->render();
    }
    
    public function getPixelsCount(): int
    {
        return $this->adapter->getWidth() * $this->adapter->getHeight();
    }

    /**
     * Get the image dimensions.
     * 
     * @return array{width: int, height: int}
     */
    public function getDimensions(): array
    {
        return [
            'width' => $this->adapter->getWidth(),
            'height' => $this->adapter->getHeight(),
        ];
    }

    public function __destruct()
    {
        $this->adapter->destroy();
    }
}
