#!/usr/bin/env php
<?php
/**
 * Batch Process - Secure all PDFs in directory dengan opsi mode
 * 
 * Usage: php batch_secure_v2.php <directory> [mode] [watermark|image_path]
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
    echo "Usage: php batch_secure_v2.php <directory> [mode] [watermark]\n";
    echo "\nMode options:\n";
    echo "  watermark  - Add watermark only (no password)\n";
    echo "  password   - Add password only (no watermark)\n";
    echo "  both       - Add both watermark and password (default)\n";
    echo "\nExamples:\n";
    echo "  php batch_secure_v2.php files/\n";
    echo "  php batch_secure_v2.php documents/ watermark \"CONFIDENTIAL\"\n";
    echo "  php batch_secure_v2.php reports/ password\n";
    echo "  php batch_secure_v2.php files/ both \"DRAFT\"\n";
    exit(1);
}

$directory = rtrim($argv[1], '/');
$mode = isset($argv[2]) ? $argv[2] : 'both';
$watermark = isset($argv[3]) ? $argv[3] : 'CONFIDENTIAL';

// Validate mode
if (!in_array($mode, ['watermark', 'password', 'both'])) {
    echo "❌ Error: Invalid mode '{$mode}'. Use: watermark, password, or both\n";
    exit(1);
}

// Check if directory exists
if (!is_dir($directory)) {
    echo "❌ Error: Directory not found: {$directory}\n";
    exit(1);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           Batch PDF Secure - SLiMS Library                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get all PDF files
$pdfFiles = glob($directory . '/*.pdf');

if (empty($pdfFiles)) {
    echo "⚠️  No PDF files found in: {$directory}\n";
    exit(0);
}

echo "Directory: {$directory}\n";
echo "Mode:      {$mode}\n";

if ($mode === 'watermark' || $mode === 'both') {
    $isImage = file_exists($watermark) && preg_match('/\.(png|jpg|jpeg|gif)$/i', $watermark);
    $wmType = $isImage ? 'Image' : 'Text';
    echo "Watermark ({$wmType}): {$watermark}\n";
}

echo "Found:     " . count($pdfFiles) . " PDF file(s)\n\n";

// Create output directory
$outputDir = $directory . '/secured';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✓ Created output directory: {$outputDir}\n\n";
}

// Statistics
$stats = [
    'processed' => 0,
    'skipped' => 0,
    'failed' => 0
];

// Password log file (only if password mode)
$logFile = $outputDir . '/passwords.txt';
if (($mode === 'password' || $mode === 'both') && !file_exists($logFile)) {
    $header = "# PDF Passwords - Generated on " . date('Y-m-d H:i:s') . "\n";
    $header .= "# Mode: {$mode}\n";
    if ($mode === 'watermark' || $mode === 'both') {
        $header .= "# Watermark: {$watermark}\n";
    }
    $header .= "\n";
    $header .= str_pad('FILE', 50) . " | PASSWORD\n";
    $header .= str_repeat('-', 70) . "\n";
    file_put_contents($logFile, $header);
}

// Process each file
foreach ($pdfFiles as $index => $inputFile) {
    $num = $index + 1;
    $basename = basename($inputFile);
    
    echo "[{$num}/" . count($pdfFiles) . "] {$basename}\n";
    
    // Skip files in secured directory
    if (strpos($inputFile, '/secured/') !== false) {
        echo "   ⏭️  Skipping (already in secured folder)\n\n";
        $stats['skipped']++;
        continue;
    }
    
    // Check if already has "_secured" in name
    if (strpos($basename, '_secured') !== false) {
        echo "   ⏭️  Skipping (already has _secured in name)\n\n";
        $stats['skipped']++;
        continue;
    }
    
    // Check if already password protected
    try {
        $testPdf = new Mpdf\Mpdf();
        $testPdf->setSourceFile($inputFile);
        // If we reach here, file is not protected
    } catch (Exception $e) {
        if (stripos($e->getMessage(), 'password') !== false || 
            stripos($e->getMessage(), 'encrypted') !== false ||
            stripos($e->getMessage(), 'protected') !== false) {
            echo "   ⏭️  Skipping (already password protected)\n\n";
            $stats['skipped']++;
            continue;
        }
    }
    
    // Generate output filename
    $pathInfo = pathinfo($inputFile);
    $outputFile = $outputDir . '/' . $pathInfo['filename'] . '_secured.pdf';
    
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
        
        // Generate and set password (if needed)
        $password = null;
        if ($mode === 'password' || $mode === 'both') {
            $password = substr(md5(basename($inputFile) . date('YmdHis') . $index), 0, 10);
            $pdf->setUserPassword($password);
        }
        
        // Process
        $result = $pdf->process();
        
        if ($result) {
            echo "   ✅ Success";
            
            if ($mode === 'password' || $mode === 'both') {
                echo " | Password: {$password}";
                
                // Log password
                $logEntry = str_pad(basename($outputFile), 50) . " | {$password}\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            }
            
            echo "\n\n";
            $stats['processed']++;
        } else {
            echo "   ❌ Failed: " . $pdf->getLastError() . "\n\n";
            $stats['failed']++;
        }
        
    } catch (Exception $e) {
        echo "   ❌ Exception: " . $e->getMessage() . "\n\n";
        $stats['failed']++;
    }
}

// Show summary
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Processed: {$stats['processed']}\n";
echo "⏭️  Skipped:   {$stats['skipped']}\n";
echo "❌ Failed:    {$stats['failed']}\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (($mode === 'password' || $mode === 'both') && $stats['processed'] > 0) {
    echo "\n📝 Password log saved to: {$logFile}\n";
}

echo "\n";

/**
 * Show help message
 */
function showHelp() {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║        Batch PDF Secure - SLiMS Library v2.0                   ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "DESCRIPTION:\n";
    echo "  Secure semua PDF files dalam directory dengan opsi mode\n";
    echo "  (watermark, password, atau both)\n\n";
    
    echo "USAGE:\n";
    echo "  php batch_secure_v2.php <directory> [mode] [watermark|image]\n\n";
    
    echo "PARAMETERS:\n";
    echo "  directory        Directory containing PDF files (required)\n";
    echo "  mode             Mode: watermark | password | both (default: both)\n";
    echo "  watermark|image  Text watermark OR path to image (PNG/JPG/GIF)\n";
    echo "                   (default: CONFIDENTIAL)\n\n";
    
    echo "MODES:\n";
    echo "  watermark        Add watermark only (no password)\n";
    echo "  password         Add password only (no watermark)\n";
    echo "  both             Add both watermark and password (default)\n\n";
    
    echo "EXAMPLES:\n";
    echo "  # Default (both mode)\n";
    echo "  php batch_secure_v2.php files/\n\n";
    
    echo "  # Text watermark only\n";
    echo "  php batch_secure_v2.php documents/ watermark \"CONFIDENTIAL\"\n\n";
    
    echo "  # Image watermark only\n";
    echo "  php batch_secure_v2.php documents/ watermark logo.png\n\n";
    
    echo "  # Password only\n";
    echo "  php batch_secure_v2.php reports/ password\n\n";
    
    echo "  # Both with image watermark\n";
    echo "  php batch_secure_v2.php files/ both logo.png\n\n";
    
    echo "OUTPUT:\n";
    echo "  • Output directory: <input>/secured/\n";
    echo "  • Files: <filename>_secured.pdf\n";
    echo "  • Password log: secured/passwords.txt (if mode=password or both)\n\n";
    
    echo "WATERMARK TYPES:\n";
    echo "  • Text: Any text string (e.g., \"CONFIDENTIAL\")\n";
    echo "  • Image: Path to PNG/JPG/GIF file (e.g., logo.png)\n";
    echo "    - Auto-detected if file exists and has image extension\n";
    echo "    - Default size: 200px width, auto height\n";
    echo "    - Same image applied to all PDFs\n\n";
    
    echo "FEATURES:\n";
    echo "  • Auto-skip already protected files\n";
    echo "  • Auto-skip files with '_secured' in name\n";
    echo "  • Auto-generate unique passwords\n";
    echo "  • Show statistics (processed/skipped/failed)\n\n";
    
    echo "OPTIONS:\n";
    echo "  --help, -h       Show this help message\n\n";
}
