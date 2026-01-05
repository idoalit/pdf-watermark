# CLI Tools Index

Command-line tools for PdfWatermark.

## 🎯 Recommended Tools

### secure_pdf_v2.php
Quick tool to secure a single file with mode options.

**Usage:**
```bash
php secure_pdf_v2.php <input.pdf> [mode] [watermark] [password]
```

**Mode:**
- `watermark` - Watermark only
- `password` - Password only
- `both` - Both (default)

### batch_secure_v2.php
Batch processing for folders with mode options.

**Usage:**
```bash
php batch_secure_v2.php <directory> [mode] [watermark]
```

---

## 📖 Complete Documentation

See [../docs/CLI_TOOLS_MODE.md](../docs/CLI_TOOLS_MODE.md) for complete documentation with examples.

---

## 📜 Legacy Tools

- `secure_pdf.php` - Legacy version (always both mode)
- `batch_secure.php` - Legacy version (always both mode)
- `add_watermark_password.php` - Advanced CLI tool with many options

