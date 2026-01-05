<?php
/**
 * Contoh Integrasi PdfWatermark dalam Admin Module
 * 
 * Simpan file ini sebagai referensi untuk integrasi dalam module
 * 
 * Path: lib/PdfWatermark/PdfWatermark.php
 * Namespace: SLiMS\PdfWatermark\PdfWatermark
 */

// ============================================
// EXAMPLE 1: Dalam Admin Module Controller
// ============================================

/*
use SLiMS\PdfWatermark\PdfWatermark;

class BibliographyController {
    
    public function downloadSecuredPdf($biblioId) {
        try {
            // Get bibliography data
            $biblio = Bibliography::find($biblioId);
            
            if (!$biblio) {
                throw new Exception("Bibliography tidak ditemukan");
            }
            
            // Get original PDF
            $originalPath = UPLOADS_DIR . 'documents/' . $biblio->file;
            
            if (!file_exists($originalPath)) {
                throw new Exception("File PDF tidak ditemukan");
            }
            
            // Set output path
            $outputDir = UPLOADS_DIR . 'secured_pdfs/';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            $outputPath = $outputDir . 'biblio_' . $biblioId . '_' . time() . '.pdf';
            
            // Create watermark
            require_once LIB_DIR . 'PdfWatermark/PdfWatermark.php';
            
            $pdf = new PdfWatermark($originalPath, $outputPath);
            
            // Add watermark with user info and security
            $watermarkText = 'User: ' . $_SESSION['user_name'] . ' | Date: ' . date('Y-m-d');
            $password = $biblio->id . '-' . date('Ymd');
            
            if ($pdf->processWithAll($watermarkText, $password)) {
                // Download file
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $biblio->title . '.pdf"');
                header('Content-Length: ' . filesize($outputPath));
                readfile($outputPath);
                
                // Optional: Delete file setelah download
                // unlink($outputPath);
            } else {
                throw new Exception($pdf->getLastError());
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
*/

// ============================================
// EXAMPLE 2: Dalam Admin Function
// ============================================

/*
use SLiMS\PdfWatermark\PdfWatermark;

function createSecuredBibliographyPdf($biblioId, $watermarkText, $password) {
    
    try {
        $biblio = Bibliography::find($biblioId);
        if (!$biblio) return false;
        
        $inputPath = UPLOADS_DIR . 'documents/' . $biblio->file;
        if (!file_exists($inputPath)) return false;
        
        $outputPath = UPLOADS_DIR . 'secured/' . basename($biblio->file);
        
        $pdf = new PdfWatermark($inputPath, $outputPath);
        
        return $pdf->processWithAll(
            $watermarkText,
            $password,
            'admin_master_password',
            [
                'opacity' => 0.3,
                'angle'   => 45,
                'size'    => 70
            ]
        );
    } catch (Exception $e) {
        error_log("PDF Watermark Error: " . $e->getMessage());
        return false;
    }
}

// Usage:
// createSecuredBibliographyPdf(123, 'CONFIDENTIAL', 'secure123');
*/

// ============================================
// EXAMPLE 3: Admin Action untuk Bulk Processing
// ============================================

/*
use SLiMS\PdfWatermark\PdfWatermark;

class BibliographyAdmin {
    
    public function applyWatermarkBulk($biblioIds, $watermarkText, $password) {
        
        $results = [
            'success' => [],
            'failed' => []
        ];
        
        foreach ($biblioIds as $biblioId) {
            try {
                $biblio = Bibliography::find($biblioId);
                if (!$biblio) {
                    $results['failed'][$biblioId] = 'Bibliography not found';
                    continue;
                }
                
                $inputPath = UPLOADS_DIR . 'documents/' . $biblio->file;
                if (!file_exists($inputPath)) {
                    $results['failed'][$biblioId] = 'File not found';
                    continue;
                }
                
                $outputPath = UPLOADS_DIR . 'secured/' . $biblioId . '_' . basename($biblio->file);
                
                $pdf = new PdfWatermark($inputPath, $outputPath);
                
                if ($pdf->processWithAll($watermarkText, $password)) {
                    $results['success'][] = $biblioId;
                } else {
                    $results['failed'][$biblioId] = $pdf->getLastError();
                }
            } catch (Exception $e) {
                $results['failed'][$biblioId] = $e->getMessage();
            }
        }
        
        return $results;
    }
}
*/

// ============================================
// EXAMPLE 4: Dalam AJAX Handler
// ============================================

/*
use SLiMS\PdfWatermark\PdfWatermark;

if ($_POST['action'] == 'secure_pdf') {
    
    $biblioId = intval($_POST['biblio_id']);
    $watermark = isset($_POST['watermark']) ? $_POST['watermark'] : 'SECURED';
    $password = isset($_POST['password']) ? $_POST['password'] : 'default123';
    
    try {
        $biblio = Bibliography::find($biblioId);
        
        $inputPath = UPLOADS_DIR . 'documents/' . $biblio->file;
        $outputPath = UPLOADS_DIR . 'temp/' . time() . '_' . $biblio->file;
        
        $pdf = new PdfWatermark($inputPath, $outputPath);
        
        if ($pdf->processWithAll($watermark, $password)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'PDF secured successfully',
                'file' => $outputPath
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => $pdf->getLastError()
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
*/

// ============================================
// EXAMPLE 5: Dengan Config & Constants
// ============================================

/*
// Tambahkan ke config/app.php atau sysconfig.inc.php

define('PDF_WATERMARK_ENABLED', true);
define('PDF_DEFAULT_WATERMARK', 'COMPANY CONFIDENTIAL');
define('PDF_WATERMARK_OPACITY', 0.3);
define('PDF_WATERMARK_ANGLE', 45);
define('PDF_WATERMARK_SIZE', 70);
define('PDF_REQUIRE_PASSWORD', true);
define('PDF_MASTER_PASSWORD', 'master_secure_password');

// Helper function
function secureLibraryPdf($inputPath, $outputPath, $biblioId = null) {
    if (!PDF_WATERMARK_ENABLED) {
        copy($inputPath, $outputPath);
        return true;
    }
    
    use SLiMS\PdfWatermark\PdfWatermark;
    
    $pdf = new PdfWatermark($inputPath, $outputPath);
    
    $watermark = PDF_DEFAULT_WATERMARK;
    if ($biblioId) {
        $watermark .= ' (#' . $biblioId . ')';
    }
    
    return $pdf->processWithAll(
        $watermark,
        'default_password', // atau bisa generate dari biblioId
        PDF_MASTER_PASSWORD,
        [
            'opacity' => PDF_WATERMARK_OPACITY,
            'angle'   => PDF_WATERMARK_ANGLE,
            'size'    => PDF_WATERMARK_SIZE
        ]
    );
}

// Usage: secureLibraryPdf($input, $output, $biblioId);
*/

// ============================================
// EXAMPLE 6: Error Handling Best Practice
// ============================================

/*
use SLiMS\PdfWatermark\PdfWatermark;

function secureDocumentWithErrorLog($inputPath, $outputPath, $watermark, $password) {
    
    try {
        if (!file_exists($inputPath)) {
            throw new Exception("Input file not found: {$inputPath}");
        }
        
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                throw new Exception("Cannot create output directory: {$outputDir}");
            }
        }
        
        $pdf = new PdfWatermark($inputPath, $outputPath);
        $pdf->setWatermarkText($watermark)
            ->setUserPassword($password);
        
        if (!$pdf->process()) {
            $errors = $pdf->getErrors();
            throw new Exception("PDF processing failed: " . implode(", ", $errors));
        }
        
        if (!file_exists($outputPath)) {
            throw new Exception("Output file was not created");
        }
        
        return [
            'success' => true,
            'outputPath' => $outputPath,
            'fileSize' => filesize($outputPath)
        ];
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        error_log("[PDF_WATERMARK_ERROR] {$errorMsg} | Input: {$inputPath} | Output: {$outputPath}");
        
        return [
            'success' => false,
            'error' => $errorMsg
        ];
    }
}

// Usage:
// $result = secureDocumentWithErrorLog($input, $output, 'TEST', 'pass123');
// if (!$result['success']) {
//     echo $result['error'];
// }
*/

// ============================================
// EXAMPLE 7: Integrasi dengan Logger
// ============================================

/*
use SLiMS\PdfWatermark\PdfWatermark;

function createSecuredPdfWithLogging($biblioId, $userId) {
    require_once LIB_DIR . 'AdvancedLogging.php';
    
    $logger = AdvancedLogging::getLogger();
    
    try {
        $biblio = Bibliography::find($biblioId);
        $user = User::find($userId);
        
        $inputPath = UPLOADS_DIR . 'documents/' . $biblio->file;
        $outputPath = UPLOADS_DIR . 'secured/' . $biblio->id . '_' . time() . '.pdf';
        
        $watermark = 'User: ' . $user->name . ' | Date: ' . date('Y-m-d H:i:s');
        $password = md5($user->id . $biblio->id . date('Y-m-d'));
        
        $pdf = new PdfWatermark($inputPath, $outputPath);
        
        if ($pdf->processWithAll($watermark, $password)) {
            $logger->info("PDF secured successfully", [
                'biblio_id' => $biblioId,
                'user_id' => $userId,
                'output' => $outputPath
            ]);
            return $outputPath;
        } else {
            $logger->error("PDF securing failed", [
                'biblio_id' => $biblioId,
                'user_id' => $userId,
                'error' => $pdf->getLastError()
            ]);
            return false;
        }
    } catch (Exception $e) {
        $logger->error("Exception while securing PDF", [
            'biblio_id' => $biblioId,
            'user_id' => $userId,
            'exception' => $e->getMessage()
        ]);
        return false;
    }
}
*/

echo "Lihat file ini untuk berbagai contoh integrasi PdfWatermark\n";
?>
