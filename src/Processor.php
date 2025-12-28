<?php

declare(strict_types=1);

namespace Steganography;

use Steganography\Compressor\CompressorInterface;
use Steganography\Compressor\ZlibCompressor;
use Steganography\Encoder\DefaultEncoder;
use Steganography\Encoder\EncoderInterface;
use Steganography\Exception\DecodeException;
use Steganography\Exception\ImageTooSmallException;
use Steganography\Image\Adapter\GdAdapter;
use Steganography\Image\Adapter\ImageAdapterInterface;
use Steganography\Image\Adapter\ImagickAdapter;
use Steganography\Image\Image;
use Steganography\Iterator\BinaryIterator;
use RuntimeException;
use Throwable;

/**
 * Main processor for encoding and decoding hidden messages in images.
 * 
 * Uses LSB (Least Significant Bit) steganography to hide data invisibly.
 * Prioritizes Imagick adapter with GD fallback for compatibility.
 */
class Processor
{
    public const BITS_PER_PIXEL = 3;
    public const LENGTH_BITS = 48;

    private CompressorInterface $compressor;
    private EncoderInterface $encoder;
    private ?string $preferredAdapter = null;

    public function __construct(
        ?CompressorInterface $compressor = null,
        ?EncoderInterface $encoder = null
    ) {
        $this->encoder = $encoder ?? new DefaultEncoder();
        $this->compressor = $compressor ?? new ZlibCompressor();
    }

    /**
     * Encode a message into an image.
     *
     * @param string $filePath Path to the source image (supports JPG, PNG, GIF)
     * @param string $message  The message to hide
     * @return Image The modified image (must be saved as PNG to preserve data)
     * @throws ImageTooSmallException If the image cannot hold the message
     * @throws RuntimeException If no suitable image adapter is available
     */
    public function encode(string $filePath, string $message): Image
    {
        $image = $this->loadImage($filePath);
        $encodedMessage = $this->encoder->encode($message, $this->compressor);
        
        // BinaryIterator prepends 48-bit length header
        $totalBits = self::LENGTH_BITS + strlen($encodedMessage);
        $pixelsNeeded = (int) ceil($totalBits / self::BITS_PER_PIXEL);
        
        if ($pixelsNeeded > $image->getPixelsCount()) {
            throw new ImageTooSmallException($pixelsNeeded, $image->getPixelsCount());
        }

        $image->setBinaryString(new BinaryIterator($encodedMessage, self::BITS_PER_PIXEL));

        return $image;
    }

    /**
     * Decode a hidden message from an image.
     *
     * @param string $filePath Path to the encoded image (must be PNG for accurate decoding)
     * @return string The decoded message
     * @throws DecodeException If decoding fails
     * @throws RuntimeException If no suitable image adapter is available
     */
    public function decode(string $filePath): string
    {
        $image = $this->loadImage($filePath);
        $binary = $image->getBinaryString(self::BITS_PER_PIXEL, self::LENGTH_BITS);

        try {
            return $this->encoder->decode($binary, $this->compressor);
        } catch (Throwable $e) {
            throw new DecodeException('Failed to decode: ' . $e->getMessage());
        }
    }

    /**
     * Calculate the maximum message size (in bytes) that can be stored in an image.
     */
    public function calculateMaxMessageSize(string $filePath): int
    {
        $image = $this->loadImage($filePath);
        $totalPixels = $image->getPixelsCount();
        
        // Total available bits = pixels * 3 (RGB LSBs) - 48 (length header)
        $availableBits = ($totalPixels * self::BITS_PER_PIXEL) - self::LENGTH_BITS;
        
        // After base64 encoding, data expands by ~4/3, and compression varies
        // Conservative estimate: 1 byte -> 8 bits -> ~11 bits after base64
        return (int) floor($availableBits / 11);
    }

    public function setCompressor(CompressorInterface $compressor): self
    {
        $this->compressor = $compressor;
        return $this;
    }

    public function setEncoder(EncoderInterface $encoder): self
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * Force a specific adapter ('imagick' or 'gd').
     */
    public function setPreferredAdapter(string $adapter): self
    {
        $this->preferredAdapter = strtolower($adapter);
        return $this;
    }

    private function loadImage(string $filePath): Image
    {
        $adapter = $this->createAdapter($filePath);
        return new Image($adapter);
    }

    private function createAdapter(string $filePath): ImageAdapterInterface
    {
        // If user specified a preference
        if ($this->preferredAdapter === 'gd') {
            return $this->createGdAdapter($filePath);
        }
        
        if ($this->preferredAdapter === 'imagick') {
            return $this->createImagickAdapter($filePath);
        }

        // Auto-detect: prioritize Imagick
        if (extension_loaded('imagick') && class_exists(\Imagick::class)) {
            try {
                return $this->createImagickAdapter($filePath);
            } catch (Throwable) {
                // Fallback to GD
            }
        }

        if (extension_loaded('gd')) {
            return $this->createGdAdapter($filePath);
        }

        throw new RuntimeException('No suitable image adapter found. Please install ext-imagick or ext-gd.');
    }

    private function createImagickAdapter(string $filePath): ImagickAdapter
    {
        $adapter = new ImagickAdapter();
        $adapter->load($filePath);
        return $adapter;
    }

    private function createGdAdapter(string $filePath): GdAdapter
    {
        $adapter = new GdAdapter();
        $adapter->load($filePath);
        return $adapter;
    }
}
