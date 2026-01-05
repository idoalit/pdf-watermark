#!/usr/bin/env php
<?php
/**
 * CLI Tool untuk menambahkan watermark dan password ke file PDF
 * 
 * Usage:
 *   php add_watermark_password.php --input=file.pdf --output=file_secured.pdf
 *   php add_watermark_password.php --dir=files/ --watermark="CONFIDENTIAL"
 *   php add_watermark_password.php --help
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

class PdfWatermarkCLI
{
    private $options = [];
    private $stats = [
        'processed' => 0,
        'skipped' => 0,
        'failed' => 0
    ];

    public function __construct($argv)
    {
        $this->parseArguments($argv);
    }

    private function parseArguments($argv)
    {
        foreach ($argv as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = isset($parts[1]) ? $parts[1] : true;
                $this->options[$key] = $value;
            }
        }
    }

    public function run()
    {
        // Show help
        if (isset($this->options['help']) || isset($this->options['h'])) {
            $this->showHelp();
            return;
        }

        // Check if processing directory or single file
        if (isset($this->options['dir'])) {
            $this->processDirectory($this->options['dir']);
        } elseif (isset($this->options['input'])) {
            $this->processSingleFile($this->options['input']);
        } else {
            $this->interactiveMode();
        }

        // Show statistics
        $this->showStats();
    }

    private function showHelp()
    {
        echo "\n╔════════════════════════════════════════════════════════════════╗\n";
        echo "║       PDF Watermark & Password Tool - SLiMS Library           ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";

        echo "USAGE:\n";
        echo "  Single File:\n";
        echo "    php add_watermark_password.php --input=file.pdf --output=output.pdf\n\n";
        
        echo "  Directory (Batch):\n";
        echo "    php add_watermark_password.php --dir=files/\n\n";
        
        echo "  Interactive Mode:\n";
        echo "    php add_watermark_password.php\n\n";

        echo "OPTIONS:\n";
        echo "  --input=PATH          Input PDF file\n";
        echo "  --output=PATH         Output PDF file (optional, default: input_secured.pdf)\n";
        echo "  --dir=PATH            Process all PDFs in directory\n";
        echo "  --watermark=TEXT      Watermark text (default: CONFIDENTIAL)\n";
        echo "  --image=PATH          Watermark image (PNG/JPG/GIF)\n";
        echo "  --password=PASS       User password (default: auto-generated)\n";
        echo "  --opacity=0.0-1.0     Watermark opacity (default: 0.3)\n";
        echo "  --angle=DEGREE        Watermark angle (default: 45)\n";
        echo "  --width=PIXELS        Image width for image watermark (default: 200)\n";
        echo "  --skip-protected      Skip files that are already protected\n";
        echo "  --force               Force overwrite without checking\n";
        echo "  --help, -h            Show this help\n\n";

        echo "EXAMPLES:\n";
        echo "  # Text watermark with password\n";
        echo "  php add_watermark_password.php --input=report.pdf --watermark=\"DRAFT\"\n\n";
        
        echo "  # Image watermark\n";
        echo "  php add_watermark_password.php --input=doc.pdf --image=logo.png --width=150\n\n";
        
        echo "  # Batch process directory\n";
        echo "  php add_watermark_password.php --dir=files/ --watermark=\"CONFIDENTIAL\"\n\n";
    }

    private function interactiveMode()
    {
        echo "\n╔════════════════════════════════════════════════════════════════╗\n";
        echo "║           PDF Watermark & Password - Interactive Mode          ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";

        // Ask for input file or directory
        echo "Choose mode:\n";
        echo "  1. Single file\n";
        echo "  2. Batch process directory\n";
        $mode = $this->prompt("Enter choice [1-2]", "1");

        if ($mode == "2") {
            $dir = $this->prompt("Enter directory path");
            if (empty($dir) || !is_dir($dir)) {
                echo "❌ Invalid directory!\n";
                return;
            }
            
            $this->options['dir'] = $dir;
            $this->options['watermark'] = $this->prompt("Watermark text", "CONFIDENTIAL");
            $this->options['password'] = $this->prompt("Password (leave empty for auto)", "");
            $this->options['skip-protected'] = $this->prompt("Skip already protected files? [y/n]", "y") === 'y';
            
            $this->processDirectory($dir);
        } else {
            $input = $this->prompt("Input PDF file path");
            if (empty($input) || !file_exists($input)) {
                echo "❌ File not found!\n";
                return;
            }

            $this->options['input'] = $input;
            
            // Ask watermark type
            $wmType = $this->prompt("Watermark type: [1] Text, [2] Image", "1");
            
            if ($wmType == "2") {
                $this->options['image'] = $this->prompt("Image path (PNG/JPG/GIF)");
                $this->options['width'] = $this->prompt("Image width (px)", "200");
            } else {
                $this->options['watermark'] = $this->prompt("Watermark text", "CONFIDENTIAL");
            }
            
            $this->options['password'] = $this->prompt("Password (leave empty for auto)", "");
            $this->options['output'] = $this->prompt("Output file (leave empty for auto)", "");
            
            $this->processSingleFile($input);
        }
    }

    private function prompt($message, $default = null)
    {
        if ($default !== null) {
            echo "{$message} [{$default}]: ";
        } else {
            echo "{$message}: ";
        }
        
        $input = trim(fgets(STDIN));
        return empty($input) ? $default : $input;
    }

    private function processSingleFile($inputPath)
    {
        echo "\n" . str_repeat("─", 60) . "\n";
        echo "Processing: {$inputPath}\n";
        echo str_repeat("─", 60) . "\n";

        // Check if file exists
        if (!file_exists($inputPath)) {
            echo "❌ File not found: {$inputPath}\n";
            $this->stats['failed']++;
            return false;
        }

        // Check if already protected
        if (!isset($this->options['force']) && $this->isPasswordProtected($inputPath)) {
            echo "⚠️  File is already password protected\n";
            
            if (isset($this->options['skip-protected']) && $this->options['skip-protected']) {
                echo "⏭️  Skipping...\n";
                $this->stats['skipped']++;
                return false;
            }
        }

        // Determine output path
        $outputPath = $this->getOutputPath($inputPath);

        // Get watermark settings
        $watermark = isset($this->options['watermark']) ? $this->options['watermark'] : 'CONFIDENTIAL';
        $password = isset($this->options['password']) && !empty($this->options['password']) 
            ? $this->options['password'] 
            : $this->generatePassword($inputPath);
        
        $opacity = isset($this->options['opacity']) ? floatval($this->options['opacity']) : 0.3;
        $angle = isset($this->options['angle']) ? intval($this->options['angle']) : 45;

        try {
            $pdf = new PdfWatermark($inputPath, $outputPath);

            // Check if using image watermark
            if (isset($this->options['image'])) {
                $imagePath = $this->options['image'];
                $width = isset($this->options['width']) ? intval($this->options['width']) : 200;
                
                echo "📝 Adding image watermark: {$imagePath}\n";
                echo "   Width: {$width}px, Opacity: {$opacity}, Angle: {$angle}°\n";
                
                $result = $pdf->processWithImage(
                    $imagePath,
                    $password,
                    null,
                    [
                        'opacity' => $opacity,
                        'angle' => $angle,
                        'width' => $width,
                        'height' => 0
                    ]
                );
            } else {
                echo "📝 Adding text watermark: {$watermark}\n";
                echo "   Opacity: {$opacity}, Angle: {$angle}°\n";
                
                $result = $pdf->processWithAll(
                    $watermark,
                    $password,
                    null,
                    [
                        'opacity' => $opacity,
                        'angle' => $angle
                    ]
                );
            }

            if ($result) {
                echo "✅ Success!\n";
                echo "   Output: {$outputPath}\n";
                echo "   Password: {$password}\n";
                
                // Save password to log file
                $this->savePasswordLog($outputPath, $password);
                
                $this->stats['processed']++;
                return true;
            } else {
                echo "❌ Failed: " . $pdf->getLastError() . "\n";
                $this->stats['failed']++;
                return false;
            }
        } catch (Exception $e) {
            echo "❌ Exception: " . $e->getMessage() . "\n";
            $this->stats['failed']++;
            return false;
        }
    }

    private function processDirectory($dirPath)
    {
        echo "\n" . str_repeat("═", 60) . "\n";
        echo "Batch Processing Directory: {$dirPath}\n";
        echo str_repeat("═", 60) . "\n\n";

        // Get all PDF files
        $files = glob(rtrim($dirPath, '/') . '/*.pdf');
        
        if (empty($files)) {
            echo "⚠️  No PDF files found in directory\n";
            return;
        }

        echo "Found " . count($files) . " PDF file(s)\n\n";

        foreach ($files as $file) {
            $this->processSingleFile($file);
            echo "\n";
        }
    }

    private function isPasswordProtected($filePath)
    {
        try {
            // Try to read PDF with mPDF
            $mpdf = new \Mpdf\Mpdf();
            $pageCount = @$mpdf->setSourceFile($filePath);
            return false; // No error means not protected
        } catch (Exception $e) {
            // If error contains "password" or "encrypted", it's protected
            $message = strtolower($e->getMessage());
            if (strpos($message, 'password') !== false || 
                strpos($message, 'encrypted') !== false ||
                strpos($message, 'protected') !== false) {
                return true;
            }
            return false;
        }
    }

    private function getOutputPath($inputPath)
    {
        if (isset($this->options['output']) && !empty($this->options['output'])) {
            return $this->options['output'];
        }

        $info = pathinfo($inputPath);
        $dir = $info['dirname'];
        $filename = $info['filename'];
        $ext = isset($info['extension']) ? $info['extension'] : 'pdf';

        // Create output directory if processing batch
        if (isset($this->options['dir'])) {
            $outputDir = rtrim($dir, '/') . '/secured';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            return $outputDir . '/' . $filename . '_secured.' . $ext;
        }

        return $dir . '/' . $filename . '_secured.' . $ext;
    }

    private function generatePassword($inputPath)
    {
        $filename = basename($inputPath, '.pdf');
        return substr(md5($filename . date('Ymd')), 0, 8);
    }

    private function savePasswordLog($outputPath, $password)
    {
        $logDir = dirname($outputPath);
        $logFile = $logDir . '/passwords.log';
        
        $entry = date('Y-m-d H:i:s') . " | " . basename($outputPath) . " | " . $password . "\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
    }

    private function showStats()
    {
        echo "\n" . str_repeat("═", 60) . "\n";
        echo "SUMMARY\n";
        echo str_repeat("═", 60) . "\n";
        echo "✅ Processed: {$this->stats['processed']}\n";
        echo "⏭️  Skipped:   {$this->stats['skipped']}\n";
        echo "❌ Failed:    {$this->stats['failed']}\n";
        echo str_repeat("═", 60) . "\n\n";
    }
}

// Run CLI
if (php_sapi_name() === 'cli') {
    $cli = new PdfWatermarkCLI($argv);
    $cli->run();
} else {
    echo "This script must be run from command line!\n";
}
