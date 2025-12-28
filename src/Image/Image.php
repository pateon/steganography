<?php

declare(strict_types=1);

namespace Steganography\Image;

use Steganography\Image\Adapter\ImageAdapterInterface;
use Steganography\Iterator\BinaryIterator;
use Steganography\Iterator\RectIterator;
use MultipleIterator;
use LimitIterator;

class Image
{
    public function __construct(
        private ImageAdapterInterface $adapter
    ) {}

    public function setBinaryString(BinaryIterator $binary): void
    {
        $iterator = new MultipleIterator(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_ASSOC);
        $iterator->attachIterator(new RectIterator($this->adapter->getWidth(), $this->adapter->getHeight()), 'rect');
        $iterator->attachIterator($binary, 'bin');

        foreach ($iterator as $current) {
            [$x, $y] = $current['rect'];
            $bits = $current['bin']; // ['r' => bit, 'g' => bit, 'b' => bit]
            
            $this->setPixel($x, $y, $bits);
        }
    }

    public function getBinaryString(int $bitsPerPixel, int $lengthBits): string
    {
        $iterator = new RectIterator($this->adapter->getWidth(), $this->adapter->getHeight());
        $lengthBin = '';
        $dataBin = '';
        
        // Read length header
        $offset = $lengthBits / $bitsPerPixel;
        
        foreach (new LimitIterator($iterator, 0, $offset) as $pos) {
            $lengthBin .= $this->getPixel($pos[0], $pos[1]);
        }

        $bits = (int) bindec($lengthBin);
        $length = (int) ceil($bits / $bitsPerPixel);

        // Read data
        foreach (new LimitIterator($iterator, $offset, $length) as $pos) {
            $dataBin .= $this->getPixel($pos[0], $pos[1]);
        }

        return substr($dataBin, 0, $bits);
    }

    private function setPixel(int $x, int $y, array $bits): void
    {
        $color = $this->adapter->getColor($x, $y);
        
        // Modify LSB of R, G, B
        // We assume 8-bit channels (0-255)
        
        $rBin = str_pad(decbin($color->r), 8, '0', STR_PAD_LEFT);
        $gBin = str_pad(decbin($color->g), 8, '0', STR_PAD_LEFT);
        $bBin = str_pad(decbin($color->b), 8, '0', STR_PAD_LEFT);

        $color->r = bindec(substr($rBin, 0, -1) . $bits['r']);
        $color->g = bindec(substr($gBin, 0, -1) . $bits['g']);
        $color->b = bindec(substr($bBin, 0, -1) . $bits['b']);

        $this->adapter->setColor($x, $y, $color);
    }

    private function getPixel(int $x, int $y): string
    {
        $color = $this->adapter->getColor($x, $y);
        
        $rBin = str_pad(decbin($color->r), 8, '0', STR_PAD_LEFT);
        $gBin = str_pad(decbin($color->g), 8, '0', STR_PAD_LEFT);
        $bBin = str_pad(decbin($color->b), 8, '0', STR_PAD_LEFT);

        return substr($rBin, -1) . substr($gBin, -1) . substr($bBin, -1);
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

    public function __destruct()
    {
        $this->adapter->destroy();
    }
}
