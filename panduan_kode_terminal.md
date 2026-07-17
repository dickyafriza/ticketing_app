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
*   **Create & Update**: Data ditambahkan/diedit melalui mekanisme umum `Lokasi::create(...)` dan `$lokasi->update(...)`.
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
