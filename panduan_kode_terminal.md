# Panduan Pengkodean & Perintah Terminal (Fitur Management Lokasi A-D)

Dokumen ini merangkum seluruh langkah pembuatan kode dan eksekusi terminal untuk mengimplementasikan fitur **Management Lokasi** secara lengkap, beserta penjelasan mendetail terkait masing-masing fungsinya (termasuk mekanisme *Soft Delete*).

---

## 1. Poin A: Membuat Tabel & Model Lokasi (Beserta *Soft Delete*)

Langkah pertama adalah membuat struktur *database* untuk menampung data lokasi.

**Perintah Terminal:**
```bash
php artisan make:model Lokasi -m
```
*(Perintah ini bertugas men-generate 2 file sekaligus: File Model dan file Migration database).*

**Penjelasan & Kode Migration (`database/migrations/..._create_lokasis_table.php`):**
Di dalam file ini, kita mendefinisikan kolom apa saja yang akan dibuat di MySQL.
```php
public function up(): void
{
    Schema::create('lokasi', function (Blueprint $table) {
        $table->id();
        $table->string('nama_lokasi');
        $table->char('aktif', 1)->default('Y');
        $table->timestamps();
        $table->softDeletes(); // CRUCIAL: Baris ini membuat kolom 'deleted_at'
    });
}
```
* **Penjelasan `softDeletes()`**: Baris ini akan otomatis menambahkan kolom `deleted_at` (tipe data Timestamp) ke dalam tabel. Ini adalah fondasi dari fitur *Soft Delete*.

**Penjelasan & Kode Model (`app/Models/Lokasi.php`):**
Model adalah representasi tabel di dalam kode Laravel.
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lokasi extends Model
{
    use SoftDeletes; // CRUCIAL: Mengaktifkan fungsionalitas Soft Delete di Model
    
    protected $table = 'lokasi'; 
    protected $fillable = ['nama_lokasi', 'aktif'];
}
```
* **Penjelasan Trait `SoftDeletes`**: Dengan menambahkan *trait* ini, Laravel tahu bahwa jika nanti ada perintah `$lokasi->delete()`, Laravel **TIDAK AKAN** menghapusnya secara permanen dengan *query* `DELETE FROM`. Sebaliknya, Laravel akan menggunakan *query* `UPDATE` untuk sekadar mengisi kolom `deleted_at` dengan tanggal dan waktu saat ini.

---

## 2. Poin B: Mengisi Data Default (Seeder)

Fitur seeder digunakan untuk memasukkan beberapa data awal begitu *database* baru saja dibuat.

**Perintah Terminal:**
```bash
php artisan make:seeder LokasiSeeder
```

**Kode Seeder (`database/seeders/LokasiSeeder.php`):**
```php
use Illuminate\Support\Facades\DB;

public function run(): void
{
    DB::table('lokasi')->insertOrIgnore([
        ['id' => 1, 'nama_lokasi' => 'Stadion Utama', 'aktif' => 'Y', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'nama_lokasi' => 'Galeri Seni Kota', 'aktif' => 'Y', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'nama_lokasi' => 'Taman Kota', 'aktif' => 'Y', 'created_at' => now(), 'updated_at' => now()],
    ]);
}
```
*(Data ini kemudian didaftarkan untuk dijalankan secara otomatis melalui file utama `DatabaseSeeder.php`)*.

---

## 3. Poin C: Mengintegrasikan Lokasi ke Form Manajemen Event

Sesuai permintaan, isian "Lokasi" pada penambahan Event yang awalnya teks biasa, kini harus mengambil referensi dari *database* (berupa pilihan / dropdown).

**Penjelasan Kode Controller (`app/Http/Controllers/EventController.php`):**
Pada method `create` dan `edit`, kita mengambil data dari tabel lokasi:
```php
// Mengambil semua lokasi yang field 'aktif'-nya bernilai 'Y'
$lokasis = \App\Models\Lokasi::where('aktif', 'Y')->get(); 
return view('admin.events.create', compact('kategoris', 'lokasis'));
```

**Penjelasan Tampilan (`resources/views/admin/events/create.blade.php`):**
```html
<label class="block text-sm font-medium text-gray-700 mb-1">Lokasi *</label>
<select name="lokasi" required class="w-full px-4 py-2 border rounded-lg...">
    <option value="">Pilih Lokasi</option>
    @foreach($lokasis as $lok)
        <!-- Menyimpan nilainya sebagai string nama_lokasi agar tidak merusak struktur tabel event lama -->
        <option value="{{ $lok->nama_lokasi }}">{{ $lok->nama_lokasi }}</option>
    @endforeach
</select>
```

---

## 4. Poin D: CRUD & Praktik Soft Delete di Management Lokasi

Membuat menu mandiri di Admin Panel untuk mengelola Master Data Lokasi.

**Perintah Terminal:**
```bash
php artisan make:controller LokasiController
```

**Penjelasan Kode Routing (`routes/web.php`):**
```php
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('lokasi', LokasiController::class); // Otomatis mendaftarkan route index, create, store, edit, update, dan destroy
});
```
*(Tautan menuju route `admin.lokasi.index` juga telah ditambahkan pada sidebar layout `admin_layouts.blade.php`)*.

**Penjelasan Controller & Soft Delete (`LokasiController.php`):**
*   **Create & Update**: Data ditambahkan/diedit melalui mekanisme umum `Lokasi::create(...)` dan `$lokasi->update(...)`. Pada proses **Update**, jika nama lokasi berubah, kita juga memperbarui semua data teks lokasi lama di tabel `events` agar ikut tersinkronisasi:
    ```php
    $oldNama = $lokasi->nama_lokasi;
    $lokasi->update($request->all());
    if ($oldNama !== $lokasi->nama_lokasi) {
        \App\Models\Event::where('lokasi', $oldNama)->update([
            'lokasi' => $lokasi->nama_lokasi
        ]);
    }
    ```
*   **Soft Delete (`destroy`)**:
    ```php
    public function destroy(Lokasi $lokasi)
    {
        // Secara sintaks, kita memanggil fungsi delete standar.
        // Namun, karena model Lokasi kita (di Poin A) memakai "use SoftDeletes",
        // proses di bawah ini BUKAN menghapus data secara fisik dari MySQL.
        $lokasi->delete(); 
        
        return redirect()->route('admin.lokasi.index')->with('success', 'Lokasi dihapus!');
    }
    ```
    *Bagaimana membuktikannya?* Setelah tombol hapus ditekan, lokasi tersebut akan menghilang dari tampilan tabel (karena `Lokasi::paginate()` otomatis menyembunyikan data yang `deleted_at`-nya terisi). Namun, jika Anda membuka *PhpMyAdmin* atau aplikasi MySQL Anda, datanya masih utuh di sana! Inilah yang dimaksud dengan *mekanisme penghapusan data dari tampilan namun pada database tidak terhapus*.

---

## 5. Eksekusi Akhir Keseluruhan

Perintah terminal terakhir yang digunakan untuk mengkompilasi file dan menerapkan *database* dari awal adalah:
```bash
composer dump-autoload
php artisan migrate:fresh --seed
```

---

## 6. Troubleshooting: Kendala PHP/Apache Tidak Bisa Start & Cara Mengatasinya

Berikut adalah rangkuman kendala umum yang sering terjadi saat pertama kali menjalankan PHP (baik via CLI/Terminal maupun via XAMPP Control Panel) beserta solusinya:

### A. Kendala pada PHP CLI / Terminal (Error: `'php' is not recognized...`)
*   **Penyebab:** Path folder instalasi PHP (biasanya `C:\xampp\php`) belum didaftarkan di dalam *System Environment Variables* Windows.
*   **Gejala:** Ketika menjalankan perintah `php artisan ...` atau `php -v` di CMD/PowerShell, muncul pesan error:
    ```text
    'php' is not recognized as an internal or external command, operable program or batch file.
    ```
*   **Solusi:**
    1. Buka Windows Search, ketik **"env"** dan pilih **"Edit the system environment variables"**.
    2. Klik tombol **"Environment Variables..."** di bagian bawah.
    3. Di bagian *System variables*, cari variabel bernama **`Path`** lalu klik **"Edit..."**.
    4. Klik **"New"** dan masukkan path instalasi PHP Anda (contoh: `C:\xampp\php`).
    5. Klik **"OK"** pada semua jendela yang terbuka, lalu **restart/buka ulang terminal** Anda (VSCode / CMD / PowerShell).

### B. Kendala Apache / PHP Tidak Bisa Start di XAMPP (Port Conflict)
*   **Penyebab:** Port standar HTTP (`80`) atau HTTPS (`443`) yang dibutuhkan oleh Apache di XAMPP telah digunakan oleh aplikasi lain.
*   **Aplikasi yang sering bentrok:** Skype, VMware, IIS (Internet Information Services), atau layanan internal Windows bernama *World Wide Web Publishing Service (W3SVC)*.
*   **Gejala:** Saat menekan tombol **"Start"** pada Apache di XAMPP Control Panel, status berubah menjadi hijau sebentar lalu kembali merah, disertai log error seperti:
    ```text
    [Apache] Port 80 in use by "Unable to open process" with PID 4!
    [Apache] Apache shutdown unexpectedly.
    ```
*   **Solusi 1 (Mematikan Layanan Windows yang bentrok):**
    1. Buka Windows Search, ketik **"services.msc"** dan tekan Enter.
    2. Cari layanan bernama **"World Wide Web Publishing Service"**.
    3. Klik kanan pada layanan tersebut, pilih **Properties**.
    4. Ubah *Startup type* menjadi **Manual** atau **Disabled**, lalu klik **Stop** jika sedang berjalan. Klik **Apply** & **OK**.
    5. Coba jalankan kembali Apache di XAMPP.
*   **Solusi 2 (Mengubah Port Apache):**
    1. Buka XAMPP Control Panel.
    2. Pada baris **Apache**, klik tombol **Config** lalu pilih **Apache (httpd.conf)**.
    3. Cari baris `Listen 80` dan ubah menjadi `Listen 8080`.
    4. Cari juga baris `ServerName localhost:80` dan ubah menjadi `ServerName localhost:8080`. Simpan file.
    5. Klik lagi tombol **Config** lalu pilih **Apache (httpd-ssl.conf)**.
    6. Cari baris `Listen 443` dan ubah menjadi `Listen 4433`. Simpan file.
    7. Start Apache. Anda sekarang bisa mengakses web dengan url: `http://localhost:8080/ticketing_app/public`.

### C. Kendala Hak Akses (Permission Denied)
*   **Penyebab:** XAMPP diinstall di folder sistem (`C:\Program Files` atau langsung di `C:\`) dan tidak mendapat izin menulis file log/temporary tanpa hak administrator.
*   **Solusi:** Tutup XAMPP Control Panel secara penuh. Cari ikon XAMPP Control Panel, klik kanan dan pilih **"Run as Administrator"**.

### D. Kendala Extension PHP Nonaktif (Error: `PHP Extension ... is missing`)
*   **Penyebab:** Composer atau Laravel membutuhkan library tertentu (misal: `sqlite3`, `gd`, `zip`, `fileinfo`, `pdo_mysql`) namun masih dinonaktifkan di konfigurasi default PHP.
*   **Solusi:**
    1. Di XAMPP Control Panel, klik **Config** pada baris Apache, lalu pilih **PHP (php.ini)**.
    2. Cari nama extension yang bermasalah (contoh: `;extension=zip` atau `;extension=pdo_mysql`).
    3. Hapus tanda titik koma (`;`) di depan extension tersebut untuk mengaktifkannya (ubah menjadi `extension=zip` atau `extension=pdo_mysql`).
    4. Simpan file `php.ini` lalu restart service Apache / MySQL Anda.

