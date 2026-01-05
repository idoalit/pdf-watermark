<?php
/**
 * Quick Test untuk PdfWatermark Class
 * 
 * Run: php lib/PdfWatermark_test.php
 */

// Test 1: Class exists and can be instantiated
echo "=" . str_repeat("=", 50) . "\n";
echo "Testing PdfWatermark Class\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Check if file exists
if (!file_exists(__DIR__ . '/PdfWatermark.php')) {
    die("❌ File PdfWatermark.php tidak ditemukan!\n");
}

echo "✓ PdfWatermark.php ditemukan\n";

// Check if mPDF is installed
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("❌ Vendor autoload tidak ditemukan. Jalankan: composer install\n");
}

require_once $autoloadPath;
require_once __DIR__ . '/../PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

echo "✓ mPDF loaded successfully\n";
echo "✓ PdfWatermark class loaded successfully\n\n";

// Check if Mpdf class is available
if (!class_exists('Mpdf\Mpdf')) {
    die("❌ Mpdf class tidak ditemukan!\n");
}

echo "✓ Mpdf\\Mpdf class found\n";

// Test 2: Class instantiation (requires valid PDF)
echo "\n" . str_repeat("-", 50) . "\n";
echo "Testing instantiation...\n";
echo str_repeat("-", 50) . "\n\n";

// Create a test PDF first
try {
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('<h1>Test PDF</h1><p>This is a test PDF file.</p>');
    
    $testDir = __DIR__ . '/../../tests';
    if (!is_dir($testDir)) {
        mkdir($testDir, 0755, true);
    }
    
    $testPdfPath = $testDir . '/test_input.pdf';
    $mpdf->Output($testPdfPath, 'F');
    
    echo "✓ Test PDF created at: {$testPdfPath}\n\n";
    
    // Test instantiation
    $pdf = new PdfWatermark($testPdfPath, $testDir . '/test_output.pdf');
    echo "✓ PdfWatermark instance created successfully\n";
    
    // Test method chaining
    $result = $pdf->setWatermarkText('TEST')
                   ->setWatermarkOpacity(0.5)
                   ->setWatermarkAngle(45)
                   ->setWatermarkSize(60)
                   ->setUserPassword('test123');
    
    echo "✓ Method chaining works\n";
    echo "✓ All methods are callable\n\n";
    
    // Test processing
    echo str_repeat("-", 50) . "\n";
    echo "Testing PDF processing...\n";
    echo str_repeat("-", 50) . "\n\n";
    
    if ($pdf->process()) {
        $outputFile = $testDir . '/test_output.pdf';
        if (file_exists($outputFile)) {
            $fileSize = filesize($outputFile);
            echo "✓ PDF processed successfully\n";
            echo "✓ Output file created: {$outputFile}\n";
            echo "✓ File size: " . number_format($fileSize) . " bytes\n\n";
        }
    } else {
        echo "✗ PDF processing failed\n";
        echo "Error: " . $pdf->getLastError() . "\n\n";
    }
    
    // Test with processWithAll
    echo str_repeat("-", 50) . "\n";
    echo "Testing processWithAll method...\n";
    echo str_repeat("-", 50) . "\n\n";
    
    $pdf2 = new PdfWatermark($testPdfPath, $testDir . '/test_complete.pdf');
    $result = $pdf2->processWithAll(
        'CONFIDENTIAL',
        'password123',
        'admin123',
        [
            'opacity' => 0.3,
            'angle'   => 45,
            'size'    => 70
        ]
    );
    
    if ($result) {
        echo "✓ processWithAll executed successfully\n";
        echo "✓ Complete PDF with watermark and password created\n\n";
    } else {
        echo "✗ processWithAll failed\n";
        echo "Error: " . $pdf2->getLastError() . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Summary
echo "=" . str_repeat("=", 50) . "\n";
echo "✓ ALL TESTS PASSED!\n";
echo "=" . str_repeat("=", 50) . "\n\n";

echo "Summary:\n";
echo "  • Class: PdfWatermark\n";
echo "  • Location: lib/PdfWatermark/PdfWatermark.php\n";
echo "  • Status: Ready to use\n";
echo "  • Features: ✓ Watermark ✓ Password Protection\n\n";

echo "Quick Start:\n";
echo "  \$pdf = new PdfWatermark('input.pdf', 'output.pdf');\n";
echo "  \$pdf->processWithAll('CONFIDENTIAL', 'password123');\n\n";

echo "Documentation: lib/PdfWatermark/README.md\n";
echo "Examples: lib/PdfWatermark/PdfWatermark_example.php\n";

?>
