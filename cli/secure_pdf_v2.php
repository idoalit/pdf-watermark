#!/usr/bin/env php
<?php
/**
 * Quick Script - Secure PDF file dengan opsi mode
 * 
 * Usage: php secure_pdf_v2.php input.pdf [mode] [watermark|image_path] [password]
 * Mode: watermark | password | both (default: both)
 * Watermark: text atau path ke image file (PNG/JPG/GIF)
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

// Check for help
if ($argc > 1 && (in_array('--help', $argv) || in_array('-h', $argv))) {
    showHelp();
    exit(0);
}

// Check arguments
if ($argc < 2) {
    echo "Usage: php secure_pdf_v2.php <input.pdf> [mode] [watermark] [password]\n";
    echo "\nMode options:\n";
    echo "  watermark  - Add watermark only (no password)\n";
    echo "  password   - Add password only (no watermark)\n";
    echo "  both       - Add both watermark and password (default)\n";
    echo "\nExamples:\n";
    echo "  php secure_pdf_v2.php document.pdf\n";
    echo "  php secure_pdf_v2.php document.pdf watermark \"CONFIDENTIAL\"\n";
    echo "  php secure_pdf_v2.php document.pdf password \"\" mypass123\n";
    echo "  php secure_pdf_v2.php document.pdf both \"DRAFT\" secret\n";
    exit(1);
}

$inputFile = $argv[1];
$mode = isset($argv[2]) ? $argv[2] : 'both';
$watermark = isset($argv[3]) ? $argv[3] : 'CONFIDENTIAL';
$password = isset($argv[4]) ? $argv[4] : null;

// Validate mode
if (!in_array($mode, ['watermark', 'password', 'both'])) {
    echo "❌ Error: Invalid mode '{$mode}'. Use: watermark, password, or both\n";
    exit(1);
}

// Check if file exists
if (!file_exists($inputFile)) {
    echo "❌ Error: File not found: {$inputFile}\n";
    exit(1);
}

// Generate output filename
$pathInfo = pathinfo($inputFile);
$outputFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_secured.pdf';

// Auto-generate password if needed and mode requires it
if (($mode === 'password' || $mode === 'both') && empty($password)) {
    $password = substr(md5(basename($inputFile) . date('Ymd')), 0, 10);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              Quick PDF Secure - SLiMS Library                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Input:  {$inputFile}\n";
echo "Output: {$outputFile}\n";
echo "Mode:   {$mode}\n";

if ($mode === 'watermark' || $mode === 'both') {
    // Check if watermark is an image
    $isImage = file_exists($watermark) && preg_match('/\.(png|jpg|jpeg|gif)$/i', $watermark);
    $wmType = $isImage ? 'Image' : 'Text';
    echo "Watermark ({$wmType}): {$watermark}\n";
}

if ($mode === 'password' || $mode === 'both') {
    echo "Password:  {$password}\n";
}

echo "\nProcessing...\n";

try {
    $pdf = new PdfWatermark($inputFile, $outputFile);
    
    // Set watermark (if needed)
    if ($mode === 'watermark' || $mode === 'both') {
        // Check if watermark is an image file
        if (file_exists($watermark) && preg_match('/\.(png|jpg|jpeg|gif)$/i', $watermark)) {
            // Image watermark
            $pdf->setWatermarkImage($watermark, 200, 0)
                ->setWatermarkOpacity(0.3)
                ->setWatermarkAngle(45);
        } else {
            // Text watermark
            $pdf->setWatermarkText($watermark)
                ->setWatermarkOpacity(0.3)
                ->setWatermarkAngle(45);
        }
    }
    
    // Set password (if needed)
    if ($mode === 'password' || $mode === 'both') {
        $pdf->setUserPassword($password);
    }
    
    // Process
    $result = $pdf->process();
    
    if ($result) {
        echo "✅ Success!\n\n";
        echo "Secured PDF saved to: {$outputFile}\n";
        
        // Save password log (if password mode)
        if ($mode === 'password' || $mode === 'both') {
            $logFile = $pathInfo['dirname'] . '/passwords.txt';
            $logEntry = date('Y-m-d H:i:s') . " | " . basename($outputFile) . " | {$password}\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            echo "Password saved to: {$logFile}\n";
        }
        
        echo "\n";
        exit(0);
    } else {
        echo "❌ Failed: " . $pdf->getLastError() . "\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n\n";
    exit(1);
}

/**
 * Show help message
 */
function showHelp() {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║           Quick PDF Secure - SLiMS Library v2.0                ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "DESCRIPTION:\n";
    echo "  Secure single PDF file dengan opsi mode (watermark, password, atau both)\n\n";
    
    echo "USAGE:\n";
    echo "  php secure_pdf_v2.php <input.pdf> [mode] [watermark|image] [password]\n\n";
    
    echo "PARAMETERS:\n";
    echo "  input.pdf        Input PDF file (required)\n";
    echo "  mode             Mode: watermark | password | both (default: both)\n";
    echo "  watermark|image  Text watermark OR path to image (PNG/JPG/GIF)\n";
    echo "                   (default: CONFIDENTIAL)\n";
    echo "  password         Password (default: auto-generated)\n\n";
    
    echo "MODES:\n";
    echo "  watermark        Add watermark only (no password)\n";
    echo "  password         Add password only (no watermark)\n";
    echo "  both             Add both watermark and password (default)\n\n";
    
    echo "EXAMPLES:\n";
    echo "  # Default (both mode with auto password)\n";
    echo "  php secure_pdf_v2.php document.pdf\n\n";
    
    echo "  # Text watermark only\n";
    echo "  php secure_pdf_v2.php document.pdf watermark \"CONFIDENTIAL\"\n\n";
    
    echo "  # Image watermark only\n";
    echo "  php secure_pdf_v2.php document.pdf watermark logo.png\n\n";
    
    echo "  # Password only\n";
    echo "  php secure_pdf_v2.php document.pdf password\n\n";
    
    echo "  # Both (image watermark + password)\n";
    echo "  php secure_pdf_v2.php document.pdf both logo.png mypass123\n\n";
    
    echo "OUTPUT:\n";
    echo "  • Secured PDF: <input>_secured.pdf\n";
    echo "  • Password log: passwords.txt (if mode=password or both)\n\n";
    
    echo "WATERMARK TYPES:\n";
    echo "  • Text: Any text string (e.g., \"CONFIDENTIAL\")\n";
    echo "  • Image: Path to PNG/JPG/GIF file (e.g., logo.png)\n";
    echo "    - Auto-detected if file exists and has image extension\n";
    echo "    - Default size: 200px width, auto height\n\n";
    
    echo "OPTIONS:\n";
    echo "  --help, -h       Show this help message\n\n";
}
