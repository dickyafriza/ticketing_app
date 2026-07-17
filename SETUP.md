# Panduan Setup Project (Ticketing App) Setelah Clone dari GitHub

Ikuti langkah-langkah di bawah ini untuk menjalankan project ini di komputer (PC/Laptop) Anda dari awal setelah melakukan clone dari GitHub.

---

## 🛠️ Prasyarat (Prerequisites)
Sebelum memulai, pastikan komputer Anda sudah terinstall:
1. **PHP** (minimal versi 8.3)
2. **Composer** (untuk mengelola dependensi PHP)
3. **Node.js & NPM** (untuk mengelola asset frontend/Vite)
4. **Database Server** (XAMPP / Laragon / MySQL Server lokal)

---

## 🚀 Langkah-Langkah Instalasi

### 1. Clone Repository & Masuk ke Folder Project
Jika Anda belum melakukan clone, jalankan perintah berikut di terminal:
```bash
git clone <URL_REPOSITORY_ANDA>
cd ticketing_app
```

### 2. Install Dependensi PHP (Composer)
Jalankan perintah ini untuk mengunduh semua package PHP yang dibutuhkan:
```bash
composer install
```

### 3. Salin Konfigurasi `.env`
Buat file konfigurasi `.env` baru dengan menyalin dari `.env.example`:
```bash
cp .env.example .env
```

### 4. Generate Application Key
Jalankan perintah ini untuk membuat key enkripsi unik untuk aplikasi Laravel Anda:
```bash
php artisan key:generate
```

### 5. Buat Database Baru
1. Buka **XAMPP Control Panel** dan aktifkan module **Apache** & **MySQL**.
2. Buka browser dan pergi ke **http://localhost/phpmyadmin**.
3. Buat database baru dengan nama: **`ticket_app`** (atau sesuaikan dengan nama yang Anda inginkan).

### 6. Konfigurasi Koneksi Database di `.env`
Buka file `.env` di text editor Anda (VSCode/Sublime/Notepad++) dan sesuaikan bagian koneksi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_app
DB_USERNAME=root
DB_PASSWORD=
```
> 💡 *Catatan: Jika menggunakan XAMPP default, `DB_USERNAME` adalah `root` dan `DB_PASSWORD` biarkan kosong.*

### 7. Jalankan Migrasi Database & Seeding (Data Default)
Perintah ini akan membuat struktur tabel baru di database dan mengisi data awal (seperti Akun Admin/User dan Kategori):
```bash
php artisan migrate --seed
```

### 8. Buat Link Storage (Penting untuk Gambar)
Laravel menggunakan symbolic link agar file/gambar event yang diupload di folder `storage` bisa diakses secara publik melalui folder `public`:
```bash
php artisan storage:link
```

### 9. Install Dependensi Frontend & Jalankan Compiler
Aplikasi ini menggunakan **Vite** untuk memproses CSS/JavaScript (Tailwind/Bootstrap/JS):

**Install library frontend:**
```bash
npm install
```

**Jalankan compiler Vite:**
- **Untuk Mode Development (Aktif memantau perubahan file):**
  ```bash
  npm run dev
  ```
- **Untuk Mode Production (Satu kali compile untuk siap tayang):**
  ```bash
  npm run build
  ```

### 10. Jalankan Server Laravel
Untuk menjalankan server lokal Laravel:
```bash
php artisan serve
```
Buka browser dan akses url default: **http://127.0.0.1:8000** atau **http://localhost:8000**.

---

## 🔑 Akun Uji Coba Default (Seeder)
Gunakan akun berikut untuk masuk ke dashboard:

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@gmail.com` | `password` |
| **Regular User** | `user@gmail.com` | `password` |

---

## ⚠️ Troubleshooting Umum

*   **Error: `Target class [App\Http\Controllers\...] does not exist`**
    Jalankan perintah: `composer dump-autoload`
*   **Error: Gambar Event tidak muncul**
    Pastikan folder `public/storage` sudah terbentuk. Jika bermasalah, hapus folder shortcut `public/storage` secara manual terlebih dahulu, lalu jalankan kembali: `php artisan storage:link`.
*   **Error database connection refused / MySQL / PHP Apache gagal start**
    1. Pastikan MySQL di XAMPP / Laragon sudah menyala (running).
    2. Jika Apache gagal start karena *Port Conflict* (biasanya port 80/443 dipakai aplikasi lain), silakan matikan *World Wide Web Publishing Service* atau ubah port Apache.
    3. Jika perintah `php` di terminal tidak dikenali (*not recognized*), daftarkan path PHP (misal `C:\xampp\php`) ke *Environment Variables* Windows.
    4. Selengkapnya mengenai kendala ini dan cara solusinya, silakan baca **[Panduan Kode Terminal - Bagian Troubleshooting](file:///c:/xampp/htdocs/ticketing_app/panduan_kode_terminal.md#6-troubleshooting-kendala-phpapache-tidak-bisa-start--cara-mengatasinya)**.

