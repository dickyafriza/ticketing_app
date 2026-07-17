# Panduan Membuat Fitur Baru (New Feature Workflow) di Laravel

Panduan ini menjelaskan alur kerja (*workflow*) langkah-demi-langkah saat Anda ingin menambahkan fitur baru pada aplikasi ticketing ini.

---

## 📂 Alur Kerja Pengembangan Fitur Baru

Untuk membuat fitur baru (contoh: **Fitur Voucher / Promo**), ikuti 7 langkah standar berikut:

### 1. Buat Git Branch Baru
Selalu buat branch baru dari branch utama (`main`) agar kode Anda tetap rapi dan tidak mengganggu kode produksi.
```bash
# Pindah ke branch main dan tarik pembaruan terbaru
git checkout main
git pull origin main

# Buat dan pindah ke branch fitur baru
# Format: feat/nama-fitur
git checkout -b feat/voucher-promo
```

---

### 2. Buat Database Migration & Model
Jika fitur baru membutuhkan penyimpanan data baru (tabel baru di database):
```bash
# Membuat Model sekaligus file Migration (-m)
php artisan make:model Voucher -m
```
* Perintah ini akan menghasilkan dua file:
  1. **Model**: `app/Models/Voucher.php`
  2. **Migration**: `database/migrations/xxxx_xx_xx_xxxxxx_create_vouchers_table.php`

**Edit file Migration** untuk menentukan kolom/tabel database:
```php
public function up(): void
{
    Schema::create('vouchers', function (Table $table) {
        $table->id();
        $table->string('kode')->unique();
        $table->integer('potongan_harga');
        $table->integer('kuota');
        $table->timestamps();
    });
}
```
**Jalankan Migration** untuk membuat tabel di database Anda:
```bash
php artisan migrate
```

---

### 3. Buat Form Request (Validasi Input)
Gunakan Form Request agar validasi form input tidak menumpuk di Controller.
```bash
php artisan make:request StoreVoucherRequest
```
* File baru akan dibuat di `app/Http/Requests/StoreVoucherRequest.php`.
* Tentukan aturan validasi di dalam method `rules()`:
```php
public function authorize(): bool
{
    return true; // Ubah ke true jika semua user terautentikasi boleh mengakses
}

public function rules(): array
{
    return [
        'kode' => 'required|string|unique:vouchers,kode|max:50',
        'potongan_harga' => 'required|integer|min:0',
        'kuota' => 'required|integer|min:1',
    ];
}
```

---

### 4. Buat Controller
Controller berfungsi untuk mengatur logika bisnis aplikasi (mengambil data dari database, memproses input, dll).
```bash
# Membuat controller dengan method CRUD bawaan (--resource)
php artisan make:controller VoucherController --resource
```
* File baru akan dibuat di `app/Http/Controllers/VoucherController.php`.
* Lengkapi method-method seperti `index()`, `store()`, `edit()`, dan `destroy()`. Contoh:
```php
use App\Models\Voucher;
use App\Http\Requests\StoreVoucherRequest;

public function store(StoreVoucherRequest $request)
{
    // Mengambil data tervalidasi
    $validated = $request->validated();

    // Simpan ke database
    Voucher::create($validated);

    return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat!');
}
```

---

### 5. Buat Route (Rute Halaman)
Daftarkan rute URL baru Anda di dalam file **`routes/web.php`** (atau `routes/api.php` jika berupa API).
```php
use App\Http\Controllers\VoucherController;

// Contoh jika fitur hanya boleh diakses oleh Admin yang sudah login
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('vouchers', VoucherController::class);
});
```

---

### 6. Buat Views (Tampilan Blade HTML)
Buat folder baru di bawah `resources/views/` sesuai nama fitur Anda, lalu buat file template Blade di dalamnya:
- `resources/views/vouchers/index.blade.php` (Menampilkan daftar data)
- `resources/views/vouchers/create.blade.php` (Form tambah data)
- `resources/views/vouchers/edit.blade.php` (Form edit data)

Gunakan layout admin yang sudah ada di project dengan `@extends('layouts.app')` atau komponen layout yang relevan.

---

### 7. Commit & Push Perubahan
Setelah fitur berhasil diuji secara lokal, lakukan commit dan push perubahan Anda ke GitHub:
```bash
# Periksa file apa saja yang diubah
git status

# Tambahkan perubahan ke staging area
git add .

# Buat commit dengan deskripsi yang jelas
git commit -m "feat: implementasi fitur voucher dan promo diskon"

# Push branch fitur ke GitHub
git push origin feat/voucher-promo
```

Setelah push, Anda bisa membuka repository di browser untuk membuat **Pull Request (PR)** ke branch `main`.

---

## 💡 Tips & Standar Penulisan Kode Laravel
1. **Gunakan Eloquent Relationship**: Jika tabel baru memiliki hubungan dengan tabel lain (misal Voucher dimiliki oleh Event), definisikan relasi `hasMany` / `belongsTo` di masing-masing Model.
2. **Gunakan Helper Route**: Jangan menulis URL hardcode di view. Gunakan helper `route('nama.route')`.
3. **Patuhi Keamanan (CSRF)**: Selalu sertakan tag `@csrf` pada setiap form HTML di dalam Blade.
4. **Gunakan Soft Deletes** jika data yang dihapus masih perlu disimpan riwayatnya di database (dengan menambahkan `$table->softDeletes()` di migration dan `use SoftDeletes` di Model).
