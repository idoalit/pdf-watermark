# PdfWatermark Library

📦 Library for adding watermarks and password protection to PDF files in SLiMS.

**Namespace**: `SLiMS\PdfWatermark`

## 📁 Folder Structure

```
lib/PdfWatermark/
├── PdfWatermark.php              # Main class
├── README.md                      # Main documentation (this file)
│
├── cli/                           # Command-line tools
│   ├── secure_pdf_v2.php         # Quick single file ⭐ (recommended)
│   ├── batch_secure_v2.php       # Batch processing ⭐ (recommended)
│   ├── secure_pdf.php            # Legacy version
│   ├── batch_secure.php          # Legacy version
│   └── add_watermark_password.php # Advanced CLI tool
│
├── examples/                      # Usage examples
│   ├── PdfWatermark_example.php
│   └── PdfWatermark_integration_examples.php
│
├── tests/                         # Test files
│   ├── PdfWatermark_test.php
│   ├── test_image_watermark.php
│   └── test_namespace.php
│
├── docs/                          # Complete documentation
│   ├── PdfWatermark_README.md    # API documentation
│   ├── CLI_TOOLS_MODE.md         # CLI docs ⭐ (recommended)
│   ├── CLI_TOOLS.md              # CLI docs legacy
│   ├── IMAGE_WATERMARK_FEATURE.md
│   ├── PDFWATERMARK_INSTALL.md
│   ├── PDFWATERMARK_CHANGELOG.md
│   └── NAMESPACE_UPDATE.md
│
├── assets/                        # Asset files (images, samples)
│   ├── logo-big.png
│   ├── Logo-Polines-96dpi-200px.png
│   └── sample.pdf
│
└── temp/                          # Temporary/generated files
    ├── passwords.log
    └── *_secured.pdf
```

## 🚀 Quick Start

### 1. Usage in PHP Code

```php
require_once 'lib/PdfWatermark/PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

// Basic: text watermark + password
$pdf = new PdfWatermark('input.pdf', 'output.pdf');
$pdf->processWithAll('CONFIDENTIAL', 'password123');

// Image watermark
$pdf = new PdfWatermark('input.pdf', 'output.pdf');
$pdf->processWithImage('assets/logo.png', 'password123', null, [
    'width' => 200,
    'opacity' => 0.3
]);
```

### 2. Command-line (Terminal)

```bash
# Single file - watermark only
php lib/PdfWatermark/cli/secure_pdf_v2.php file.pdf watermark "CONFIDENTIAL"

# Single file - password only
php lib/PdfWatermark/cli/secure_pdf_v2.php file.pdf password

# Batch - watermark + password
php lib/PdfWatermark/cli/batch_secure_v2.php folder/ both "CONFIDENTIAL"
```

**Note:** CLI path changed to `cli/` folder. See [FOLDER_STRUCTURE.md](FOLDER_STRUCTURE.md) for migration guide.

Complete CLI documentation: [docs/CLI_TOOLS_MODE.md](docs/CLI_TOOLS_MODE.md)

## 📚 Documentation

### Main Documentation
- **[docs/PdfWatermark_README.md](docs/PdfWatermark_README.md)** - Complete documentation with API reference
- **[docs/CLI_TOOLS_MODE.md](docs/CLI_TOOLS_MODE.md)** ⭐ - CLI tools with mode options (recommended)
- **[docs/PDFWATERMARK_INSTALL.md](docs/PDFWATERMARK_INSTALL.md)** - Installation and setup guide
- **[docs/IMAGE_WATERMARK_FEATURE.md](docs/IMAGE_WATERMARK_FEATURE.md)** - Image watermark feature

### Examples & Testing
- **[examples/PdfWatermark_example.php](examples/PdfWatermark_example.php)** - Various usage examples
- **[examples/PdfWatermark_integration_examples.php](examples/PdfWatermark_integration_examples.php)** - Integration examples
- **[tests/](tests/)** - Test suite

## ✨ Features

- ✅ Text watermark on every PDF page
- ✅ Image watermark (PNG/JPG/GIF) ⭐
- ✅ Control opacity, angle, and watermark size
- ✅ Password protection (User & Owner password)
- ✅ Mode options: watermark only, password only, or both ⭐
- ✅ CLI tools for terminal
- ✅ Batch processing for folders
- ✅ Auto password generation
- ✅ Multi-page PDF support
- ✅ Method chaining
- ✅ Comprehensive error handling

## 🧪 Testing

```bash
# Basic test
php lib/PdfWatermark/tests/PdfWatermark_test.php

# Image watermark test
php lib/PdfWatermark/tests/test_image_watermark.php

# Namespace test
php lib/PdfWatermark/tests/test_namespace.php
```


See [examples/](examples/) folder for various examples:

- **[examples/PdfWatermark_example.php](examples/PdfWatermark_example.php)** - 8+ complete examples
- **[examples/PdfWatermark_integration_examples.php](examples/PdfWatermark_integration_examples.php)** - Integration into SLiMS modules

## 📝 Asset Files

Sample PDF and watermark images available in [assets/](assets/) folder:
- `sample.pdf` - Sample PDF for testing
- `logo-big.png` - Sample logo
- `Logo-Polines-96dpi-200px.png` - Sample Polines logo

## 📦 Dependencies

- **mpdf/mpdf** ^8.1 (already installed)
- PHP 7.2+
- GD Library

## 💡 Usage Examples

### 1. Watermark Only
```php
require_once 'lib/PdfWatermark/PdfWatermark.php';

use SLiMS\PdfWatermark\PdfWatermark;

$pdf = new PdfWatermark('input.pdf', 'output.pdf');
$pdf->setWatermarkText('DRAFT')
    ->setWatermarkOpacity(0.3)
    ->process();
```

### 2. Password Only
```php
use SLiMS\PdfWatermark\PdfWatermark;

$pdf = new PdfWatermark('input.pdf', 'output.pdf');
$pdf->setUserPassword('secret123')
    ->process();
```

### 3. Watermark + Password (Recommended)
```php
use SLiMS\PdfWatermark\PdfWatermark;

$pdf = new PdfWatermark('input.pdf', 'output.pdf');
$pdf->processWithAll(
    'CONFIDENTIAL',           // watermark
    'password123',            // user password
    'admin_password',         // owner password
    [
        'opacity' => 0.25,
        'angle'   => 45,
        'size'    => 70
    ]
);
```

### 4. Image Watermark (Logo) ⭐ NEW!
```php
use SLiMS\PdfWatermark\PdfWatermark;

$pdf = new PdfWatermark('input.pdf', 'output.pdf');

// With method chaining
$pdf->setWatermarkImage('path/to/logo.png', 200, 0) // width: 200px, height: auto
    ->setWatermarkOpacity(0.3)
    ->setWatermarkAngle(0)
    ->process();

// Or with shortcut method
$pdf->processWithImage(
    'path/to/logo.png',
    'password123',
    null,
    ['opacity' => 0.3, 'width' => 200]
);
```

## 🔧 Integration

See [PdfWatermark_integration_examples.php](PdfWatermark_integration_examples.php) for integration examples in:
- Admin Module Controller
- Admin Functions
- Bulk Processing
- AJAX Handler
- Config & Constants
- Error Handling & Logging

## 📝 Status

- ✅ **Production Ready**
- ✅ All tests passed
- ✅ Fully documented
- ✅ Integration examples provided

## 🆘 Support

If you have questions or issues:
1. Read [PdfWatermark_README.md](PdfWatermark_README.md) - Complete documentation
2. See [PdfWatermark_example.php](PdfWatermark_example.php) - Examples
3. Run test: `php lib/PdfWatermark/PdfWatermark_test.php`

---

**Version**: 1.0.0  
**Date**: December 28, 2025  
**Status**: Ready for Production ✅

