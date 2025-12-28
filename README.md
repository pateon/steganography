# Steganography

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.5-8892BF.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Latest Version](https://img.shields.io/github/v/tag/pateon/steganography?label=version)](https://github.com/pateon/steganography)

A PHP 8.5+ library for invisible anti-counterfeiting of images using LSB (Least Significant Bit) Steganography.
This library hides a secret message inside an image file, invisible to the human eye.

## Features

- ✅ **Invisible**: Modifies only the Least Significant Bits (LSB) of RGB pixel values
- ✅ **Compression**: Compresses messages using Zlib before embedding
- ✅ **Unicode Support**: Full support for UTF-8, including CJK characters and emoji
- ✅ **Dual Driver Support**: Prioritizes `Imagick`, falls back to `GD` automatically
- ✅ **Format Support**: Reads JPG, PNG, GIF, WebP, BMP as source images
- ✅ **Lossless Output**: Always outputs as PNG to preserve hidden data
- ✅ **PHP 8.5+**: Uses modern PHP features (typed constants, readonly properties, match expressions)
- ✅ **Composer Ready**: PSR-4 autoloading, ready for integration

## Requirements

- PHP >= 8.5
- `ext-zlib` (for compression)
- `ext-imagick` (recommended) **OR** `ext-gd` (at least one is required)

## Installation

```bash
composer require pateon/steganography
```

## Usage

### Basic Usage

```php
use Steganography\Processor;

$processor = new Processor();

// Encode a message into an image
$image = $processor->encode('source.jpg', 'This is my secret message! 秘密訊息');
$image->save('output.png'); // MUST save as PNG (lossless)

// Decode the message from the image
$message = $processor->decode('output.png');
echo $message; // "This is my secret message! 秘密訊息"
```

### Force Specific Adapter

```php
$processor = new Processor();

// Force GD
$processor->setPreferredAdapter('gd');

// Force Imagick
$processor->setPreferredAdapter('imagick');
```

### Calculate Maximum Message Size

```php
$processor = new Processor();
$maxBytes = $processor->calculateMaxMessageSize('source.jpg');
echo "Max message size: ~{$maxBytes} bytes";
```

### Custom Compressor

```php
use Steganography\Processor;
use Steganography\Compressor\ZlibCompressor;

$processor = new Processor(
    compressor: new ZlibCompressor(level: 9) // Maximum compression
);
```

## ⚠️ Important Notes

1. **Output Format**: Always save encoded images as **PNG**. JPG compression is lossy and will destroy the hidden data.

2. **Source Format**: JPG, PNG, GIF, WebP, and BMP are supported as source images.

3. **Image Size**: Larger images can store more data. Use `calculateMaxMessageSize()` to check capacity.

4. **Imagick vs GD**: Imagick is preferred for better performance. GD is used as fallback.

## Architecture

```
src/
├── Processor.php              # Main entry point
├── Compressor/
│   ├── CompressorInterface.php
│   └── ZlibCompressor.php     # Zlib compression
├── Encoder/
│   ├── EncoderInterface.php
│   └── DefaultEncoder.php     # Base64 + Binary encoding
├── Exception/
│   ├── SteganographyException.php
│   ├── ImageTooSmallException.php
│   └── DecodeException.php
├── Image/
│   ├── Image.php              # LSB steganography logic
│   ├── Color.php              # RGBA color DTO
│   └── Adapter/
│       ├── ImageAdapterInterface.php
│       ├── GdAdapter.php      # GD implementation
│       └── ImagickAdapter.php # Imagick implementation
└── Iterator/
    ├── BinaryIterator.php     # Binary data iterator
    └── RectIterator.php       # Pixel coordinate iterator
```

## How It Works

1. **Compression**: The message is compressed using Zlib
2. **Encoding**: Compressed data is Base64 encoded, then converted to binary (0s and 1s)
3. **Embedding**: Each pixel's RGB values have their LSB modified to store 3 bits (1 per channel)
4. **Header**: A 48-bit length header is prepended to know how much data to read when decoding

## License

MIT License - see [LICENSE](LICENSE) file
