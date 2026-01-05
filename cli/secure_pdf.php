#!/usr/bin/env php
<?php
/**
 * Quick Script - Secure single PDF file
 * 
 * Usage: php secure_pdf.php input.pdf
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
    echo "Usage: php secure_pdf.php <input.pdf> [watermark_text] [password]\n";
    echo "\nExample:\n";
    echo "  php secure_pdf.php document.pdf\n";
    echo "  php secure_pdf.php document.pdf \"CONFIDENTIAL\"\n";
    echo "  php secure_pdf.php document.pdf \"DRAFT\" mypassword123\n";
    exit(1);
}

$inputFile = $argv[1];
$watermark = isset($argv[2]) ? $argv[2] : 'CONFIDENTIAL';
$password = isset($argv[3]) ? $argv[3] : substr(md5(basename($inputFile) . date('Ymd')), 0, 10);

// Check if file exists
if (!file_exists($inputFile)) {
    echo "❌ Error: File not found: {$inputFile}\n";
    exit(1);
}

// Generate output filename
$pathInfo = pathinfo($inputFile);
$outputFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_secured.pdf';

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              Quick PDF Secure - SLiMS Library                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Input:     {$inputFile}\n";
echo "Output:    {$outputFile}\n";
echo "Watermark: {$watermark}\n";
echo "Password:  {$password}\n\n";

echo "Processing...\n";

try {
    $pdf = new PdfWatermark($inputFile, $outputFile);
    
    $result = $pdf->processWithAll(
        $watermark,
        $password,
        null,
        ['opacity' => 0.3, 'angle' => 45]
    );
    
    if ($result) {
        echo "✅ Success!\n\n";
        echo "Secured PDF saved to: {$outputFile}\n";
        echo "Password: {$password}\n\n";
        
        // Save to password log
        $logFile = $pathInfo['dirname'] . '/passwords.txt';
        $logEntry = date('Y-m-d H:i:s') . " | " . basename($outputFile) . " | Password: {$password}\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        echo "Password saved to: {$logFile}\n\n";
        
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
    echo "║      Quick PDF Secure - SLiMS Library (Legacy)                 ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "DESCRIPTION:\n";
    echo "  Secure single PDF file dengan watermark dan password\n";
    echo "  (Legacy version - always adds both watermark and password)\n\n";
    
    echo "USAGE:\n";
    echo "  php secure_pdf.php <input.pdf> [watermark_text] [password]\n\n";
    
    echo "PARAMETERS:\n";
    echo "  input.pdf        Input PDF file (required)\n";
    echo "  watermark_text   Watermark text (default: CONFIDENTIAL)\n";
    echo "  password         Password (default: auto-generated)\n\n";
    
    echo "EXAMPLES:\n";
    echo "  # Default (auto password)\n";
    echo "  php secure_pdf.php document.pdf\n\n";
    
    echo "  # Custom watermark\n";
    echo "  php secure_pdf.php document.pdf \"DRAFT\"\n\n";
    
    echo "  # Custom watermark and password\n";
    echo "  php secure_pdf.php document.pdf \"CONFIDENTIAL\" mypass123\n\n";
    
    echo "OUTPUT:\n";
    echo "  • Secured PDF: <input>_secured.pdf\n";
    echo "  • Password log: passwords.txt\n\n";
    
    echo "NOTE:\n";
    echo "  This is a legacy version. For mode options (watermark-only or\n";
    echo "  password-only), use secure_pdf_v2.php instead.\n\n";
    
    echo "OPTIONS:\n";
    echo "  --help, -h       Show this help message\n\n";
}
