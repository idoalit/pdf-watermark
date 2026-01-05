# CLI Tools - PdfWatermark dengan Mode Options

Command-line tools untuk menambahkan watermark dan/atau password ke file PDF.

## 🛠️ Tool yang Tersedia

### 1. **secure_pdf_v2.php** - Quick Single File
Tool sederhana untuk secure satu file dengan opsi mode.

### 2. **batch_secure_v2.php** - Batch Processing
Tool untuk memproses semua PDF dalam folder dengan opsi mode.

### Mode Options:
- **watermark** - Hanya tambah watermark (tidak ada password)
- **password** - Hanya tambah password (tidak ada watermark)
- **both** - Tambah watermark dan password (default)

---

## 📖 Cara Menggunakan

### 1. Quick Secure - Single File

```bash
# Basic usage (watermark + password)
php lib/PdfWatermark/secure_pdf_v2.php document.pdf

# Hanya watermark (no password)
php lib/PdfWatermark/secure_pdf_v2.php document.pdf watermark "CONFIDENTIAL"

# Hanya password (no watermark)
php lib/PdfWatermark/secure_pdf_v2.php document.pdf password

# Custom watermark + password
php lib/PdfWatermark/secure_pdf_v2.php document.pdf both "DRAFT" mypass123
```

**Parameter:**
```
php secure_pdf_v2.php <input.pdf> [mode] [watermark] [password]
```

**Output:**
- File baru: `document_secured.pdf`
- Password disimpan di: `passwords.txt` (jika mode password atau both)

---

### 2. Batch Processing - Semua File di Folder

```bash
# Process dengan watermark + password (default)
php lib/PdfWatermark/batch_secure_v2.php files/

# Hanya watermark (no password)
php lib/PdfWatermark/batch_secure_v2.php documents/ watermark "CONFIDENTIAL"

# Hanya password (no watermark)
php lib/PdfWatermark/batch_secure_v2.php files/ password

# Custom: both dengan watermark khusus
php lib/PdfWatermark/batch_secure_v2.php reports/ both "DRAFT"
```

**Parameter:**
```
php batch_secure_v2.php <directory> [mode] [watermark]
```

**Output:**
- Folder baru: `files/secured/`
- Semua file akan diberi suffix: `_secured.pdf`
- Password log: `files/secured/passwords.txt` (jika mode password atau both)

**Fitur:**
- ✅ Otomatis skip file yang sudah terproteksi
- ✅ Skip file yang sudah ada `_secured` di nama
- ✅ Generate password otomatis (jika mode password)
- ✅ Simpan log password

---

## 🎯 Use Cases

### Use Case 1: Dokumen Publik dengan Branding
Hanya perlu watermark, tidak perlu password:

```bash
# Single file
php lib/PdfWatermark/secure_pdf_v2.php report.pdf watermark "© MyCompany 2024"

# Batch
php lib/PdfWatermark/batch_secure_v2.php reports/ watermark "© MyCompany 2024"
```

**Hasil:** PDF dengan watermark tapi bisa dibuka tanpa password

---

### Use Case 2: Dokumen Confidential Tanpa Watermark Visible
Hanya perlu password, tidak perlu watermark:

```bash
# Single file dengan password custom
php lib/PdfWatermark/secure_pdf_v2.php confidential.pdf password "" secretpass123

# Batch dengan auto-generated password
php lib/PdfWatermark/batch_secure_v2.php confidential_docs/ password
```

**Hasil:** PDF terproteksi password, tidak ada watermark visible

---

### Use Case 3: Dokumen Dengan Proteksi Penuh
Watermark + Password:

```bash
# Default (both mode)
php lib/PdfWatermark/secure_pdf_v2.php document.pdf

# Explicit both mode
php lib/PdfWatermark/batch_secure_v2.php files/ both "CONFIDENTIAL"
```

**Hasil:** PDF dengan watermark DAN terproteksi password

---

## 📊 Output Examples

### Mode: watermark
```
Input:  document.pdf
Output: document_secured.pdf
Mode:   watermark
Watermark: CONFIDENTIAL

Processing...
✅ Success!

Secured PDF saved to: document_secured.pdf
```

### Mode: password
```
Input:  document.pdf
Output: document_secured.pdf
Mode:   password
Password:  a8f5c2d9e1

Processing...
✅ Success!

Secured PDF saved to: document_secured.pdf
Password saved to: passwords.txt
```

### Mode: both
```
Input:  document.pdf
Output: document_secured.pdf
Mode:   both
Watermark: CONFIDENTIAL
Password:  a8f5c2d9e1

Processing...
✅ Success!

Secured PDF saved to: document_secured.pdf
Password saved to: passwords.txt
```

---

## 📂 Output Structure

### Single File - Mode: both
```
documents/
├── original.pdf
├── original_secured.pdf    ← Output dengan watermark + password
└── passwords.txt           ← Password log
```

### Single File - Mode: watermark
```
documents/
├── original.pdf
└── original_secured.pdf    ← Output dengan watermark only
```

### Batch - Mode: both
```
files/
├── doc1.pdf
├── doc2.pdf
└── secured/                ← Folder baru
    ├── doc1_secured.pdf   ← Watermark + password
    ├── doc2_secured.pdf
    └── passwords.txt       ← Password log
```

### Batch - Mode: password
```
files/
├── doc1.pdf
├── doc2.pdf
└── secured/
    ├── doc1_secured.pdf   ← Password only
    ├── doc2_secured.pdf
    └── passwords.txt
```

---

## 💡 Tips & Best Practices

### 1. Backup Original Files
```bash
# Backup dulu sebelum batch process
cp -r files/ files_backup/
php lib/PdfWatermark/batch_secure_v2.php files/ both
```

### 2. Hanya Watermark (Tidak Perlu Password)
```bash
# Untuk dokumen publik yang hanya perlu branding
php lib/PdfWatermark/batch_secure_v2.php files/ watermark "© MyCompany 2024"
```

### 3. Hanya Password (Tidak Perlu Watermark)
```bash
# Untuk dokumen confidential tanpa watermark visible
php lib/PdfWatermark/batch_secure_v2.php files/ password
```

### 4. Test dengan Satu File Dulu
```bash
# Test dulu dengan 1 file
php lib/PdfWatermark/secure_pdf_v2.php test.pdf watermark "TEST"

# Kalau OK, baru batch
php lib/PdfWatermark/batch_secure_v2.php files/ watermark "CONFIDENTIAL"
```

### 5. Custom Password untuk File Penting
```bash
# Gunakan password sendiri (bukan auto-generated)
php lib/PdfWatermark/secure_pdf_v2.php important.pdf both "CONFIDENTIAL" mySecurePass123
```

---

## 🚨 Error Handling

### Invalid Mode
```
❌ Error: Invalid mode 'xyz'. Use: watermark, password, or both
```
**Solusi:** Gunakan mode yang valid: `watermark`, `password`, atau `both`

### File Not Found
```
❌ Error: File not found: document.pdf
```
**Solusi:** Pastikan path file benar

### Already Protected (akan di-skip)
```
⏭️  Skipping (already password protected)
```
**Solusi:** Normal, file sudah aman

---

## 📊 Batch Statistics

Setelah batch processing:

```
═══════════════════════════════════════════════
SUMMARY
═══════════════════════════════════════════════
✅ Processed: 10
⏭️  Skipped:   2
❌ Failed:    0
═══════════════════════════════════════════════

📝 Password log saved to: files/secured/passwords.txt
```

---

## 🔗 Integration dengan SLiMS

### Dalam Module - Hanya Watermark
```php
// Watermark untuk dokumen publik
exec("php lib/PdfWatermark/secure_pdf_v2.php " . 
     escapeshellarg($uploadedFile) . " watermark 'LIBRARY COPY'");
```

### Dalam Module - Hanya Password
```php
// Password untuk dokumen confidential
exec("php lib/PdfWatermark/secure_pdf_v2.php " . 
     escapeshellarg($uploadedFile) . " password");
```

### Dalam Module - Both
```php
// Full protection
exec("php lib/PdfWatermark/secure_pdf_v2.php " . 
     escapeshellarg($uploadedFile) . " both 'CONFIDENTIAL' " . 
     escapeshellarg($password));
```

### Scheduled Task (Cron)
```bash
# Watermark all public documents daily
0 2 * * * cd /path/to/slims && php lib/PdfWatermark/batch_secure_v2.php files/public/ watermark "LIBRARY"

# Secure confidential documents with password
0 3 * * * cd /path/to/slims && php lib/PdfWatermark/batch_secure_v2.php files/confidential/ password
```

---

## 🆚 Perbedaan dengan Tool Lama

### Tool Lama (secure_pdf.php, batch_secure.php)
- ❌ Selalu menambahkan watermark DAN password
- ❌ Tidak bisa pilih salah satu saja

### Tool Baru (secure_pdf_v2.php, batch_secure_v2.php)
- ✅ Bisa pilih: watermark saja, password saja, atau keduanya
- ✅ Lebih fleksibel untuk berbagai use case
- ✅ Syntax lebih jelas dengan mode parameter

---

## 📞 Quick Reference

```bash
# ══════════════════════════════════════════════════════════════
# QUICK REFERENCE - Copy & Paste Commands
# ══════════════════════════════════════════════════════════════

# SINGLE FILE
# ----------
# Watermark only
php lib/PdfWatermark/secure_pdf_v2.php file.pdf watermark "TEXT"

# Password only
php lib/PdfWatermark/secure_pdf_v2.php file.pdf password

# Both (default)
php lib/PdfWatermark/secure_pdf_v2.php file.pdf

# BATCH DIRECTORY
# ---------------
# Watermark only
php lib/PdfWatermark/batch_secure_v2.php folder/ watermark "TEXT"

# Password only
php lib/PdfWatermark/batch_secure_v2.php folder/ password

# Both (default)
php lib/PdfWatermark/batch_secure_v2.php folder/

# ══════════════════════════════════════════════════════════════
```

---

**Status:** ✅ Ready to use  
**Version:** 2.0 dengan mode options  
**Platform:** Linux, macOS, Windows (with PHP CLI)
