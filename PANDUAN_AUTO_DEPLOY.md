# 🚀 Panduan Integrasi GitHub & Auto-Deploy cPanel

Panduan ini membantu Anda menghubungkan repository GitHub `websitedesatanjungkesuma` ke cPanel agar setiap kali Anda melakukan `git push`, website otomatis terupdate.

---

## 1. Persiapan di cPanel
1.  **Login** ke Dashboard cPanel Anda.
2.  Cari menu **SSH Access**.
    *   Klik **Manage SSH Keys**.
    *   Klik **Generate a New Key**.
    *   Isi nama key (misal: `id_rsa`), buat password kuat, lalu klik **Generate Key**.
    *   Kembali ke daftar key, klik **Manage** pada key yang baru dibuat, lalu klik **Authorize**.
    *   Klik **View/Download** pada Private Key, lalu klik **Convert to PPK** (opsional) atau cukup biarkan saja.
    *   **PENTING**: Ambil isi **Public Key** dan tambahkan ke akun GitHub Anda di menu `Settings -> SSH and GPG Keys -> New SSH Key`.

## 2. Menghubungkan Repository
1.  Di cPanel, cari menu **Git™ Version Control**.
2.  Klik tombol **Create**.
3.  **Clone URL**: Masukkan `https://github.com/apriprogram/websitedesatanjungkesuma.git` (Gunakan format SSH `git@github.com:apriprogram/websitedesatanjungkesuma.git` jika menggunakan SSH Key).
4.  **Repository Path**: Isi dengan folder utama website (contoh: `public_html`).
5.  **Repository Name**: Isi bebas (contoh: `website-desa`).
6.  Klik **Create**.

## 3. Mengaktifkan Auto-Deploy
File `.cpanel.yml` sudah saya buatkan di dalam project. Sekarang Anda hanya perlu:
1.  Di menu **Git™ Version Control**, klik **Manage** pada repository Anda.
2.  Klik tab **Pull or Deploy**.
3.  Klik **Update from Remote** untuk menarik data terbaru dari GitHub.
4.  Klik **Deploy Head Revision** untuk menjalankan instruksi di `.cpanel.yml`.

---

## 💡 Cara Kerja Sehari-hari
Setelah setup selesai, setiap kali Anda selesai coding di laptop:
1.  Buka Terminal/CMD di folder project.
2.  Ketik: `git add .`
3.  Ketik: `git commit -m "Catatan perubahan Anda"`
4.  Ketik: `git push origin main`

**Website Anda akan langsung terupdate secara otomatis!**

---

## ⚠️ Catatan Penting
*   **Vendor Folder**: Folder `vendor` tidak di-upload ke GitHub. Jika website Error 500 setelah deploy pertama, masuk ke Terminal cPanel dan ketik: `composer install`.
*   **.env File**: File `.env` juga tidak di-upload demi keamanan. Pastikan Anda sudah membuat file `.env` secara manual di File Manager cPanel.
*   **Storage Link**: Pastikan jalankan `php artisan storage:link` di cPanel agar gambar muncul.

---
*Dibuat otomatis oleh Antigravity AI Coding Assistant.*
