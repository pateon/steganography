<?php

declare(strict_types=1);

/**
 * Steganography Test Script
 * 
 * Tests encoding/decoding with both GD and Imagick adapters.
 * Requires PHP 8.5+ and either ext-gd or ext-imagick.
 */

// Try Composer autoloader first, fallback to manual requires
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Manual autoloading for testing without Composer
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
}

use Steganography\Processor;

echo "=== Steganography Test (PHP " . PHP_VERSION . ") ===\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.5.0', '<')) {
    echo "Warning: This package requires PHP 8.5.0 or higher.\n";
    echo "Current version: " . PHP_VERSION . "\n\n";
}

$processor = new Processor();
$message = "Hello, this is a secret message! 隱藏訊息測試 🔐";
$testImage = __DIR__ . '/koala.jpg';

if (!file_exists($testImage)) {
    echo "Error: Test image not found: {$testImage}\n";
    echo "Please provide a test image named 'koala.jpg' in the project root.\n";
    exit(1);
}

echo "Source image: koala.jpg\n";
echo "Message to encode: '{$message}'\n";
echo "Message length: " . strlen($message) . " bytes\n";
echo "Max message size: " . $processor->calculateMaxMessageSize($testImage) . " bytes (estimated)\n\n";

$passed = 0;
$failed = 0;

try {
    // Test with GD
    if (extension_loaded('gd')) {
        echo "--- Testing with GD ---\n";
        $processor->setPreferredAdapter('gd');
        
        $startTime = microtime(true);
        $image = $processor->encode($testImage, $message);
        $encodeTime = microtime(true) - $startTime;
        
        $image->save(__DIR__ . '/encoded_gd.png');
        echo "Saved: encoded_gd.png\n";
        echo "Encode time: " . number_format($encodeTime * 1000, 2) . " ms\n";
        
        $startTime = microtime(true);
        $decoded = $processor->decode(__DIR__ . '/encoded_gd.png');
        $decodeTime = microtime(true) - $startTime;
        
        echo "Decoded: '{$decoded}'\n";
        echo "Decode time: " . number_format($decodeTime * 1000, 2) . " ms\n";
        
        if ($message === $decoded) {
            echo "✓ GD Test PASSED\n\n";
            ++$passed;
        } else {
            echo "✗ GD Test FAILED\n\n";
            ++$failed;
        }
    } else {
        echo "GD extension not available, skipping GD tests.\n\n";
    }
    
    // Test with Imagick if available
    if (extension_loaded('imagick')) {
        echo "--- Testing with Imagick ---\n";
        $processor->setPreferredAdapter('imagick');
        
        $startTime = microtime(true);
        $image = $processor->encode($testImage, $message);
        $encodeTime = microtime(true) - $startTime;
        
        $image->save(__DIR__ . '/encoded_imagick.png');
        echo "Saved: encoded_imagick.png\n";
        echo "Encode time: " . number_format($encodeTime * 1000, 2) . " ms\n";
        
        $startTime = microtime(true);
        $decoded = $processor->decode(__DIR__ . '/encoded_imagick.png');
        $decodeTime = microtime(true) - $startTime;
        
        echo "Decoded: '{$decoded}'\n";
        echo "Decode time: " . number_format($decodeTime * 1000, 2) . " ms\n";
        
        if ($message === $decoded) {
            echo "✓ Imagick Test PASSED\n\n";
            ++$passed;
        } else {
            echo "✗ Imagick Test FAILED\n\n";
            ++$failed;
        }
        
        // Cross-test: encode with Imagick, decode with GD
        if (extension_loaded('gd')) {
            echo "--- Cross-Test: Imagick -> GD ---\n";
            $processor->setPreferredAdapter('gd');
            $decoded = $processor->decode(__DIR__ . '/encoded_imagick.png');
            echo "Decoded: '{$decoded}'\n";
            
            if ($message === $decoded) {
                echo "✓ Cross Test PASSED\n\n";
                ++$passed;
            } else {
                echo "✗ Cross Test FAILED\n\n";
                ++$failed;
            }
        }
    } else {
        echo "Imagick extension not available, skipping Imagick tests.\n\n";
    }
    
} catch (Throwable $e) {
    echo "Error: {$e->getMessage()}\n";
    echo $e->getTraceAsString() . "\n";
    ++$failed;
}

// Cleanup
@unlink(__DIR__ . '/encoded_gd.png');
@unlink(__DIR__ . '/encoded_imagick.png');

// Summary
echo "=== Test Summary ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
