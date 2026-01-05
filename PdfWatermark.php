<?php
/**
 * PDF Watermark and Password Protection Class
 * 
 * Kelas untuk menambahkan watermark dan password protection pada file PDF
 * menggunakan mPDF library
 * 
 * @author SLiMS Development Team
 * @package SLiMS\PdfWatermark
 */

namespace SLiMS\PdfWatermark;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Exception;

class PdfWatermark
{
    /**
     * mPDF instance
     * @var Mpdf
     */
    private $mpdf;

    /**
     * Path to input PDF file
     * @var string
     */
    private $inputFile;

    /**
     * Path to output PDF file
     * @var string
     */
    private $outputFile;

    /**
     * Watermark text
     * @var string
     */
    private $watermarkText;

    /**
     * Watermark image path
     * @var string
     */
    private $watermarkImage;

    /**
     * Watermark type: 'text' or 'image'
     * @var string
     */
    private $watermarkType = 'text';

    /**
     * Watermark opacity (0-1)
     * @var float
     */
    private $watermarkOpacity = 0.3;

    /**
     * Watermark angle in degrees
     * @var int
     */
    private $watermarkAngle = 45;

    /**
     * Watermark size (font size for text, or scale for image)
     * @var int
     */
    private $watermarkSize = 60;

    /**
     * Watermark image width (for image watermark)
     * @var int
     */
    private $watermarkImageWidth = 200;

    /**
     * Watermark image height (for image watermark)
     * @var int
     */
    private $watermarkImageHeight = 0; // 0 = auto

    /**
     * PDF password (user password)
     * @var string
     */
    private $userPassword;

    /**
     * PDF owner password (for restrictions)
     * @var string
     */
    private $ownerPassword;

    /**
     * Error messages
     * @var array
     */
    private $errors = [];

    /**
     * Constructor
     * 
     * @param string $inputFile Path ke file PDF input
     * @param string $outputFile Path ke file PDF output
     */
    public function __construct($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            throw new Exception("File PDF input tidak ditemukan: {$inputFile}");
        }

        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;
        
        // Initialize mPDF
        $this->initializeMpdf();
    }

    /**
     * Initialize mPDF instance
     * 
     * @return void
     */
    private function initializeMpdf()
    {
        // Get temporary directory
        $tempDir = sys_get_temp_dir() . '/mpdf';
        
        // Create temp directory if not exists
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        try {
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = (new FontVariables())->getDefaults();
            
            $fontDirArray = isset($fontDirs['fontDir']) ? $fontDirs['fontDir'] : [];
        } catch (\Exception $e) {
            $fontDirArray = [];
        }

        $this->mpdf = new Mpdf([
            'tempDir' => $tempDir,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);
    }

    /**
     * Set watermark text
     * 
     * @param string $text Teks watermark
     * @return self
     */
    public function setWatermarkText($text)
    {
        $this->watermarkText = $text;
        $this->watermarkType = 'text';
        return $this;
    }

    /**
     * Set watermark image
     * 
     * @param string $imagePath Path ke file gambar watermark
     * @param int $width Lebar gambar (default: 200)
     * @param int $height Tinggi gambar (0 = auto, default: 0)
     * @return self
     */
    public function setWatermarkImage($imagePath, $width = 200, $height = 0)
    {
        if (!file_exists($imagePath)) {
            throw new Exception("File gambar watermark tidak ditemukan: {$imagePath}");
        }
        
        // Validasi tipe file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception("Tipe file tidak didukung. Gunakan: " . implode(', ', $allowedTypes));
        }
        
        $this->watermarkImage = $imagePath;
        $this->watermarkImageWidth = $width;
        $this->watermarkImageHeight = $height;
        $this->watermarkType = 'image';
        
        return $this;
    }

    /**
     * Set watermark opacity
     * 
     * @param float $opacity Opacity value (0-1)
     * @return self
     */
    public function setWatermarkOpacity($opacity)
    {
        if ($opacity < 0 || $opacity > 1) {
            throw new Exception("Opacity harus antara 0 dan 1");
        }
        $this->watermarkOpacity = $opacity;
        return $this;
    }

    /**
     * Set watermark angle
     * 
     * @param int $angle Sudut watermark dalam derajat
     * @return self
     */
    public function setWatermarkAngle($angle)
    {
        $this->watermarkAngle = $angle;
        return $this;
    }

    /**
     * Set watermark size
     * 
     * @param int $size Ukuran font watermark
     * @return self
     */
    public function setWatermarkSize($size)
    {
        $this->watermarkSize = $size;
        return $this;
    }

    /**
     * Set user password untuk membuka PDF
     * 
     * @param string $password Password untuk membuka file
     * @return self
     */
    public function setUserPassword($password)
    {
        $this->userPassword = $password;
        return $this;
    }

    /**
     * Set owner password untuk pembatasan
     * 
     * @param string $password Password pemilik untuk pembatasan
     * @return self
     */
    public function setOwnerPassword($password)
    {
        $this->ownerPassword = $password;
        return $this;
    }

    /**
     * Add watermark ke PDF
     * 
     * @return bool
     */
    public function addWatermark()
    {
        if (empty($this->watermarkText)) {
            $this->errors[] = "Teks watermark belum diatur";
            return false;
        }

        try {
            // Set watermark
            $this->mpdf->SetWatermarkText($this->watermarkText);
            $this->mpdf->watermark_font = 'DejaVuSans';
            $this->mpdf->showWatermarkText = true;
            
            // Set opacity
            $this->mpdf->SetAlpha($this->watermarkOpacity);

            return true;
        } catch (Exception $e) {
            $this->errors[] = "Error menambahkan watermark: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Add password protection ke PDF
     * 
     * @return bool
     */
    public function addPasswordProtection()
    {
        try {
            $user = $this->userPassword ?? '';
            $owner = $this->ownerPassword ?? $this->userPassword ?? '';

            if (empty($user) && empty($owner)) {
                $this->errors[] = "Password belum diatur";
                return false;
            }

            // mPDF uses SetProtection method
            // Format: SetProtection(array $permissions, string $userPassword, string $ownerPassword)
            // Permissions: print, copy, modify
            $this->mpdf->SetProtection(
                ['print', 'copy', 'modify'],
                $user,
                $owner
            );

            return true;
        } catch (Exception $e) {
            $this->errors[] = "Error menambahkan password protection: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Process PDF dengan watermark dan/atau password
     * 
     * @return bool
     */
    public function process()
    {
        try {
            // Read input PDF
            $pdfContent = file_get_contents($this->inputFile);

            if ($pdfContent === false) {
                $this->errors[] = "Gagal membaca file PDF input";
                return false;
            }

            // Reinitialize mPDF to handle the PDF properly
            $this->initializeMpdf();

            // Import PDF pages
            $pageCount = $this->mpdf->setSourceFile($this->inputFile);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $this->mpdf->importPage($pageNo);
                $this->mpdf->AddPage();
                $this->mpdf->useTemplate($templateId);
                
                // Add watermark on each page
                if ($this->watermarkType === 'image' && !empty($this->watermarkImage)) {
                    $this->addImageWatermarkToPage();
                }
            }

            // Add text watermark if set using built-in mPDF watermark
            if ($this->watermarkType === 'text' && !empty($this->watermarkText)) {
                $this->mpdf->SetWatermarkText($this->watermarkText);
                $this->mpdf->watermark_font = 'DejaVuSans';
                $this->mpdf->showWatermarkText = true;
            }

            // Add password protection if set
            if (!empty($this->userPassword) || !empty($this->ownerPassword)) {
                $this->addPasswordProtection();
            }

            // Output PDF
            $this->mpdf->Output($this->outputFile, 'F');

            return file_exists($this->outputFile);
        } catch (Exception $e) {
            $this->errors[] = "Error processing PDF: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Add image watermark to current page
     * 
     * @return void
     */
    private function addImageWatermarkToPage()
    {
        try {
            // Get page dimensions
            $pageWidth = $this->mpdf->w;
            $pageHeight = $this->mpdf->h;
            
            // Calculate position (center of page)
            $imgWidth = $this->watermarkImageWidth;
            $imgHeight = $this->watermarkImageHeight;
            
            // If height is 0 (auto), maintain aspect ratio
            if ($imgHeight == 0) {
                list($origWidth, $origHeight) = getimagesize($this->watermarkImage);
                $imgHeight = ($imgWidth / $origWidth) * $origHeight;
            }
            
            $x = ($pageWidth - $imgWidth) / 2;
            $y = ($pageHeight - $imgHeight) / 2;
            
            // Set opacity
            $this->mpdf->SetAlpha($this->watermarkOpacity);
            
            // Add image with rotation if angle is set
            if ($this->watermarkAngle != 0) {
                $centerX = $pageWidth / 2;
                $centerY = $pageHeight / 2;
                
                $this->mpdf->Rotate($this->watermarkAngle, $centerX, $centerY);
                $this->mpdf->Image($this->watermarkImage, $x, $y, $imgWidth, $imgHeight, '', '', true, false);
                $this->mpdf->Rotate(0);
            } else {
                $this->mpdf->Image($this->watermarkImage, $x, $y, $imgWidth, $imgHeight, '', '', true, false);
            }
            
            // Reset opacity
            $this->mpdf->SetAlpha(1);
            
        } catch (Exception $e) {
            $this->errors[] = "Error menambahkan image watermark: " . $e->getMessage();
        }
    }

    /**
     * Process PDF dengan semua fitur sekaligus
     * 
     * @param string $watermarkText Text untuk watermark
     * @param string $userPassword User password
     * @param string $ownerPassword Owner password (opsional)
     * @param array $options Opsi tambahan (opacity, angle, size)
     * @return bool
     */
    public function processWithAll($watermarkText, $userPassword, $ownerPassword = null, $options = [])
    {
        $this->setWatermarkText($watermarkText);
        $this->setUserPassword($userPassword);
        
        if ($ownerPassword) {
            $this->setOwnerPassword($ownerPassword);
        }

        // Set custom options if provided
        if (isset($options['opacity'])) {
            $this->setWatermarkOpacity($options['opacity']);
        }
        if (isset($options['angle'])) {
            $this->setWatermarkAngle($options['angle']);
        }
        if (isset($options['size'])) {
            $this->setWatermarkSize($options['size']);
        }

        return $this->process();
    }

    /**
     * Process PDF dengan image watermark
     * 
     * @param string $imagePath Path ke gambar watermark
     * @param string $userPassword User password
     * @param string $ownerPassword Owner password (opsional)
     * @param array $options Opsi tambahan (opacity, angle, width, height)
     * @return bool
     */
    public function processWithImage($imagePath, $userPassword = null, $ownerPassword = null, $options = [])
    {
        $width = isset($options['width']) ? $options['width'] : 200;
        $height = isset($options['height']) ? $options['height'] : 0;
        
        $this->setWatermarkImage($imagePath, $width, $height);
        
        if ($userPassword) {
            $this->setUserPassword($userPassword);
        }
        
        if ($ownerPassword) {
            $this->setOwnerPassword($ownerPassword);
        }

        // Set custom options if provided
        if (isset($options['opacity'])) {
            $this->setWatermarkOpacity($options['opacity']);
        }
        if (isset($options['angle'])) {
            $this->setWatermarkAngle($options['angle']);
        }

        return $this->process();
    }

    /**
     * Get error messages
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get last error message
     * 
     * @return string
     */
    public function getLastError()
    {
        return end($this->errors) ?: '';
    }

    /**
     * Check if there are errors
     * 
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Clear errors
     * 
     * @return void
     */
    public function clearErrors()
    {
        $this->errors = [];
    }
}
