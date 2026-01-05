<?php
/**
 * Quick namespace test
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

echo "Testing namespace...\n";

try {
    // Test class instantiation
    $testFile = sys_get_temp_dir() . '/test_namespace.pdf';
    
    // Create simple PDF first
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML('<h1>Test</h1>');
    $mpdf->Output($testFile, 'F');
    
    // Test PdfWatermark with namespace
    $pdf = new PdfWatermark($testFile, sys_get_temp_dir() . '/test_output.pdf');
    
    echo "✅ Namespace SLiMS\\PdfWatermark\\PdfWatermark works!\n";
    echo "✅ Class instantiated successfully\n";
    
    // Test method chaining
    $pdf->setWatermarkText('TEST')
        ->setUserPassword('test123');
    
    echo "✅ Method chaining works\n";
    echo "\n✅ ALL NAMESPACE TESTS PASSED!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
