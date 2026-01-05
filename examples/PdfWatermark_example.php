<?php
/**
 * Contoh Penggunaan Class PdfWatermark
 * 
 * File ini menunjukkan berbagai cara menggunakan class PdfWatermark
 */

// Include class
require_once __DIR__ . '/../PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

// ============================================
// CONTOH 1: Watermark saja (tanpa password)
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/document.pdf',
        'output/document_watermarked.pdf'
    );
    
    $pdf->setWatermarkText('CONFIDENTIAL')
        ->setWatermarkOpacity(0.3)
        ->setWatermarkAngle(45)
        ->setWatermarkSize(60);
    
    if ($pdf->process()) {
        echo "✓ Watermark berhasil ditambahkan!\n";
    } else {
        echo "✗ Error: " . $pdf->getLastError() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// CONTOH 2: Password protection saja
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/document.pdf',
        'output/document_protected.pdf'
    );
    
    $pdf->setUserPassword('password123')
        ->setOwnerPassword('admin123');
    
    if ($pdf->process()) {
        echo "✓ Password protection berhasil ditambahkan!\n";
    } else {
        echo "✗ Error: " . $pdf->getLastError() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// CONTOH 3: Watermark + Password (Complete)
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/document.pdf',
        'output/document_complete.pdf'
    );
    
    $result = $pdf->processWithAll(
        'COMPANY CONFIDENTIAL',           // watermark text
        'user_password',                  // user password (untuk membuka)
        'owner_password',                 // owner password (untuk restrictions)
        [                                 // options
            'opacity' => 0.25,
            'angle'   => 45,
            'size'    => 70
        ]
    );
    
    if ($result) {
        echo "✓ Watermark dan password berhasil ditambahkan!\n";
    } else {
        echo "✗ Error: " . $pdf->getLastError() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// CONTOH 4: Dengan error handling
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/document.pdf',
        'output/document_safe.pdf'
    );
    
    $pdf->setWatermarkText('DRAFT')
        ->setUserPassword('secure123');
    
    if ($pdf->process()) {
        echo "✓ File berhasil diproses!\n";
    } else {
        if ($pdf->hasErrors()) {
            echo "✗ Terjadi kesalahan:\n";
            foreach ($pdf->getErrors() as $error) {
                echo "  - " . $error . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// CONTOH 5: Dalam aplikasi (Integration)
// ============================================
/*
// Biasanya di dalam controller atau function

function createSecuredPdf($inputPath, $outputPath, $watermark, $password) {
    try {
        $pdf = new PdfWatermark($inputPath, $outputPath);
        
        return $pdf->processWithAll(
            $watermark,
            $password
        );
    } catch (Exception $e) {
        log_error($e->getMessage());
        return false;
    }
}

// Usage
if (createSecuredPdf('files/report.pdf', 'output/report.pdf', 'CONFIDENTIAL', 'secret123')) {
    echo "File secured successfully";
} else {
    echo "Failed to secure file";
}
*/

// ============================================
// CONTOH 6: Watermark dengan GAMBAR
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/document.pdf',
        'output/document_image_watermark.pdf'
    );
    
    // Set image watermark (PNG/JPG/GIF)
    $pdf->setWatermarkImage('path/to/logo.png', 150, 0) // width: 150px, height: auto
        ->setWatermarkOpacity(0.2)
        ->setWatermarkAngle(0); // 0 = tidak dirotasi
    
    if ($pdf->process()) {
        echo "✓ Image watermark berhasil ditambahkan!\n";
    } else {
        echo "✗ Error: " . $pdf->getLastError() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// CONTOH 7: Image Watermark + Password (Shortcut)
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/document.pdf',
        'output/document_logo_secured.pdf'
    );
    
    $result = $pdf->processWithImage(
        'images/company_logo.png',    // path ke gambar
        'password123',                 // user password
        'admin_password',              // owner password
        [
            'opacity' => 0.25,
            'angle'   => 0,
            'width'   => 200,
            'height'  => 0  // auto maintain aspect ratio
        ]
    );
    
    if ($result) {
        echo "✓ Image watermark dan password berhasil ditambahkan!\n";
    } else {
        echo "✗ Error: " . $pdf->getLastError() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// ============================================
// CONTOH 8: Watermark dengan Logo Transparan
// ============================================
try {
    $pdf = new PdfWatermark(
        'input/certificate.pdf',
        'output/certificate_watermarked.pdf'
    );
    
    // Gunakan logo PNG dengan transparency
    $pdf->setWatermarkImage('images/watermark_logo.png', 300, 100)
        ->setWatermarkOpacity(0.3)
        ->setWatermarkAngle(45)
        ->setUserPassword('cert2024');
    
    if ($pdf->process()) {
        echo "✓ Certificate watermarked successfully!\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
