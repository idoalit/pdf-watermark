#!/usr/bin/env php
<?php
/**
 * Batch Process - Secure all PDFs in a directory
 * 
 * Usage: php batch_secure.php <directory> [watermark_text]
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
    echo "Usage: php batch_secure.php <directory> [watermark_text]\n";
    echo "\nExample:\n";
    echo "  php batch_secure.php files/\n";
    echo "  php batch_secure.php documents/ \"CONFIDENTIAL\"\n";
    exit(1);
}

$directory = rtrim($argv[1], '/');
$watermark = isset($argv[2]) ? $argv[2] : 'CONFIDENTIAL';

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

echo "Directory:  {$directory}\n";
echo "Watermark:  {$watermark}\n";
echo "Found:      " . count($pdfFiles) . " PDF file(s)\n\n";

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

// Process each file
$passwordLog = [];

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
        echo "   ⏭️  Skipping (already secured)\n\n";
        $stats['skipped']++;
        continue;
    }
    
    // Generate output path and password
    $outputFile = $outputDir . '/' . pathinfo($basename, PATHINFO_FILENAME) . '_secured.pdf';
    $password = substr(md5($basename . date('Ymd')), 0, 10);
    
    try {
        // Check if already password protected
        $isProtected = false;
        try {
            $mpdf = new \Mpdf\Mpdf();
            @$mpdf->setSourceFile($inputFile);
        } catch (Exception $e) {
            $msg = strtolower($e->getMessage());
            if (strpos($msg, 'password') !== false || strpos($msg, 'encrypted') !== false) {
                $isProtected = true;
            }
        }
        
        if ($isProtected) {
            echo "   ⚠️  Already password protected, skipping...\n\n";
            $stats['skipped']++;
            continue;
        }
        
        // Process PDF
        $pdf = new PdfWatermark($inputFile, $outputFile);
        
        $result = $pdf->processWithAll(
            $watermark,
            $password,
            null,
            ['opacity' => 0.3, 'angle' => 45]
        );
        
        if ($result) {
            echo "   ✅ Success! Password: {$password}\n\n";
            $passwordLog[] = [
                'file' => basename($outputFile),
                'password' => $password
            ];
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

// Save password log
if (!empty($passwordLog)) {
    $logFile = $outputDir . '/passwords.txt';
    $logContent = "# PDF Passwords - Generated on " . date('Y-m-d H:i:s') . "\n";
    $logContent .= "# Watermark: {$watermark}\n\n";
    $logContent .= str_pad("FILE", 50) . " | PASSWORD\n";
    $logContent .= str_repeat("-", 70) . "\n";
    
    foreach ($passwordLog as $entry) {
        $logContent .= str_pad($entry['file'], 50) . " | " . $entry['password'] . "\n";
    }
    
    file_put_contents($logFile, $logContent);
    echo "📝 Password log saved: {$logFile}\n\n";
}

// Show summary
echo str_repeat("═", 60) . "\n";
echo "SUMMARY\n";
echo str_repeat("═", 60) . "\n";
echo "✅ Processed: {$stats['processed']}\n";
echo "⏭️  Skipped:   {$stats['skipped']}\n";
echo "❌ Failed:    {$stats['failed']}\n";
echo str_repeat("═", 60) . "\n\n";

if ($stats['processed'] > 0) {
    echo "✓ Secured files saved to: {$outputDir}\n";
    echo "✓ All passwords saved to: {$outputDir}/passwords.txt\n\n";
}

exit(0);

/**
 * Show help message
 */
function showHelp() {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║     Batch PDF Secure - SLiMS Library (Legacy)                  ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "DESCRIPTION:\n";
    echo "  Secure semua PDF files dalam directory dengan watermark dan password\n";
    echo "  (Legacy version - always adds both watermark and password)\n\n";
    
    echo "USAGE:\n";
    echo "  php batch_secure.php <directory> [watermark_text]\n\n";
    
    echo "PARAMETERS:\n";
    echo "  directory        Directory containing PDF files (required)\n";
    echo "  watermark_text   Watermark text (default: CONFIDENTIAL)\n\n";
    
    echo "EXAMPLES:\n";
    echo "  # Default watermark\n";
    echo "  php batch_secure.php files/\n\n";
    
    echo "  # Custom watermark\n";
    echo "  php batch_secure.php documents/ \"DRAFT\"\n\n";
    
    echo "OUTPUT:\n";
    echo "  • Output directory: <input>/secured/\n";
    echo "  • Files: <filename>_secured.pdf\n";
    echo "  • Password log: secured/passwords.txt\n\n";
    
    echo "FEATURES:\n";
    echo "  • Auto-skip already protected files\n";
    echo "  • Auto-skip files with '_secured' in name\n";
    echo "  • Auto-generate unique passwords\n";
    echo "  • Show statistics (processed/skipped/failed)\n\n";
    
    echo "NOTE:\n";
    echo "  This is a legacy version. For mode options (watermark-only or\n";
    echo "  password-only), use batch_secure_v2.php instead.\n\n";
    
    echo "OPTIONS:\n";
    echo "  --help, -h       Show this help message\n\n";
}
