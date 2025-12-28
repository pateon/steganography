<?php

require_once __DIR__ . '/src/Exception/SteganographyException.php';
require_once __DIR__ . '/src/Exception/ImageTooSmallException.php';
require_once __DIR__ . '/src/Exception/DecodeException.php';
require_once __DIR__ . '/src/Image/Color.php';
require_once __DIR__ . '/src/Image/Adapter/ImageAdapterInterface.php';
require_once __DIR__ . '/src/Image/Adapter/GdAdapter.php';
require_once __DIR__ . '/src/Image/Adapter/ImagickAdapter.php';
require_once __DIR__ . '/src/Iterator/RectIterator.php';
require_once __DIR__ . '/src/Iterator/BinaryIterator.php';
require_once __DIR__ . '/src/Image/Image.php';
require_once __DIR__ . '/src/Compressor/CompressorInterface.php';
require_once __DIR__ . '/src/Compressor/ZlibCompressor.php';
require_once __DIR__ . '/src/Encoder/EncoderInterface.php';
require_once __DIR__ . '/src/Encoder/DefaultEncoder.php';
require_once __DIR__ . '/src/Processor.php';

use Steganography\Processor;

echo "=== Steganography Test ===\n\n";

$processor = new Processor();
$message = "Hello, this is a secret message! 隱藏訊息測試 🔐";

echo "Source image: koala.jpg\n";
echo "Message to encode: '$message'\n";
echo "Max message size: " . $processor->calculateMaxMessageSize(__DIR__ . '/koala.jpg') . " bytes (estimated)\n\n";

try {
    // Test with GD
    echo "--- Testing with GD ---\n";
    $processor->setPreferredAdapter('gd');
    $image = $processor->encode(__DIR__ . '/koala.jpg', $message);
    $image->save(__DIR__ . '/encoded_gd.png');
    echo "Saved: encoded_gd.png\n";
    
    $decoded = $processor->decode(__DIR__ . '/encoded_gd.png');
    echo "Decoded: '$decoded'\n";
    echo ($message === $decoded) ? "✓ GD Test PASSED\n\n" : "✗ GD Test FAILED\n\n";
    
    // Test with Imagick if available
    if (extension_loaded('imagick')) {
        echo "--- Testing with Imagick ---\n";
        $processor->setPreferredAdapter('imagick');
        $image = $processor->encode(__DIR__ . '/koala.jpg', $message);
        $image->save(__DIR__ . '/encoded_imagick.png');
        echo "Saved: encoded_imagick.png\n";
        
        $decoded = $processor->decode(__DIR__ . '/encoded_imagick.png');
        echo "Decoded: '$decoded'\n";
        echo ($message === $decoded) ? "✓ Imagick Test PASSED\n\n" : "✗ Imagick Test FAILED\n\n";
        
        // Cross-test: encode with Imagick, decode with GD
        echo "--- Cross-Test: Imagick -> GD ---\n";
        $processor->setPreferredAdapter('gd');
        $decoded = $processor->decode(__DIR__ . '/encoded_imagick.png');
        echo "Decoded: '$decoded'\n";
        echo ($message === $decoded) ? "✓ Cross Test PASSED\n" : "✗ Cross Test FAILED\n";
    } else {
        echo "Imagick not available, skipping Imagick tests.\n";
    }
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// Cleanup
@unlink(__DIR__ . '/encoded_gd.png');
@unlink(__DIR__ . '/encoded_imagick.png');
