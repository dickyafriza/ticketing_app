# Naskah Video Demonstrasi Fitur Ticketing App

Dokumen ini berisi panduan naskah (script) video demonstrasi sistem manajemen event dan tiket pada aplikasi **Ticketing App**. Panduan ini mencakup alur visual, narasi suara (Voice Over), durasi perkiraan, serta penanda kode (code notice) lengkap dengan komentar penjelasan untuk mempermudah saat Anda melakukan rekaman dan penjelasan.

---

## Ringkasan Video
* **Durasi Target:** 3 - 5 Menit
* **Fokus Fitur:** CRUD (Create, Read, Update, Delete), Dynamic Ticket Form, Filter & Search, Gambar Cropper.
* **Resolusi Rekomendasi:** Full HD 1080p (1920x1080) dengan framerate 60fps untuk transisi yang mulus.

---

## Struktur Alur Video & Storyboard

### Adegan 1: Pembuka & Overview Aplikasi (0:00 - 0:45)
* **Visual:** Tampilan Dashboard Admin Ticketing App. Kursor berpindah ke menu navigasi sidebar, lalu mengklik menu **"Manajemen Event"** untuk masuk ke halaman daftar event.
* **Narasi (Voice Over):**
  > "Halo semuanya! Di video kali ini, saya akan mendemonstrasikan sistem manajemen event dan tiket dari aplikasi ticketing kami. Kita akan melihat fitur CRUD lengkap, pencarian dinamis, filter kategori dan status, hingga pembuatan tiket dengan form dinamis. Mari kita mulai dengan melihat halaman utama manajemen event."
* **Notice Kode yang Ditampilkan (di layar/editor):**
  * Tunjukkan routing admin event di [web.php](file:///Applications/XAMPP/xamppfiles/htdocs/ticketing_app/routes/web.php#L26-L32):
    ```php
    // Mendefinisikan grup rute untuk admin dengan prefix 'admin', nama rute diawali 'admin.', serta wajib melewati middleware 'auth' (login) dan 'verified' (email terverifikasi)
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
        
        // Rute POST untuk memproses aksi hapus massal (bulk delete) beberapa event sekaligus
        Route::post('/events/bulk-delete', [EventController::class, 'bulkDelete'])->name('events.bulkDelete');
        
        // Rute POST untuk menduplikasi atau menggandakan event beserta jenis tiketnya
        Route::post('/events/{event}/clone', [EventController::class, 'clone'])->name('events.clone');
        
        // Rute resource CRUD default Laravel (index, create, store, edit, update, destroy) untuk pengelolaan event admin
        Route::resource('events', EventController::class)->except(['show']);
    });
    ```

---

### Adegan 2: Fitur Pencarian & Filter - Read (0:45 - 1:30)
* **Visual:** Di halaman Daftar Event, tunjukkan proses mengetik kata kunci pencarian di kolom *Search* (misalnya: nama konser). Kemudian, pilih kategori dari dropdown *Kategori* dan status dari dropdown *Status*, lalu klik tombol **Filter**. Tunjukkan data di tabel yang berubah secara dinamis sesuai filter.
* **Narasi (Voice Over):**
  > "Di halaman daftar event, admin dapat dengan mudah mencari event tertentu menggunakan kolom pencarian berdasarkan judul atau lokasi. Selain itu, terdapat filter kategori serta status event (Upcoming, Ongoing, atau Completed) untuk mempermudah navigasi data dalam skala besar. Pengurutan data juga bisa dilakukan secara menaik atau menurun berdasarkan tanggal event."
* **Notice Kode yang Ditampilkan (di layar/editor):**
  * Tunjukkan logic filter & pencarian di controller [EventController.php](file:///Applications/XAMPP/xamppfiles/htdocs/ticketing_app/app/Http/Controllers/EventController.php#L20-L57):
    ```php
    public function index(Request $request)
    {
        // Mengambil query dasar event yang dibuat oleh user (admin) yang sedang login saat ini,
        // sekaligus memuat (eager load) relasi kategori dan tiket untuk efisiensi query database
        $query = auth()->user()->events()->with(['kategori', 'tikets']);

        // Fitur Pencarian: Jika admin menginputkan kata kunci pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            // Melakukan pencarian menggunakan operator 'like' pada judul event atau lokasi event
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhere('lokasi', 'like', '%' . $search . '%');
            });
        }

        // Fitur Filter Kategori: Jika admin memilih filter kategori tertentu
        if ($request->has('kategori') && $request->kategori != '') {
            // Memfilter event berdasarkan foreign key kategori_id
            $query->where('kategori_id', $request->kategori);
        }

        // Fitur Filter Status: Jika admin memilih filter status event (Upcoming, Ongoing, atau Completed)
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'Upcoming') {
                $query->upcoming(); // Memanggil local scope 'upcoming' di model Event
            } elseif ($request->status === 'Ongoing') {
                $query->ongoing(); // Memanggil local scope 'ongoing' di model Event
            } elseif ($request->status === 'Completed') {
                $query->completed(); // Memanggil local scope 'completed' di model Event
            }
        }

        // Fitur Sorting: Mengurutkan tanggal_waktu secara asc (terlama) atau desc (terbaru), default-nya asc
        $sort = $request->get('sort', 'asc');
        $query->orderBy('tanggal_waktu', $sort);

        // Membatasi hasil pencarian dengan pagination sebanyak 10 item per halaman
        $events = $query->paginate(10);
        $kategoris = Kategori::all(); // Mengambil seluruh kategori untuk dropdown filter

        // Mengembalikan tampilan (view) daftar event admin dengan data terkait
        return view('pages.admin.events.index', compact('events', 'kategoris'));
    }
    ```

---

### Adegan 3: Tambah Event Baru & Dynamic Ticket Form - Create (1:30 - 3:00)
* **Visual:** Klik tombol **"+ Tambah Event Baru"**. Isi form informasi event (Judul, Kategori, Lokasi, Tanggal & Waktu, Deskripsi). 
  * Tunjukkan fitur cropping gambar: pilih gambar poster, sesuaikan kotak crop 16:9, lalu klik **"Gunakan Potongan Ini"**.
  * Geser ke bagian **Tiket Event**. Klik tombol **"+ Tambah Tiket"** sebanyak 2-3 kali untuk menambahkan baris tiket baru secara dinamis. Isi tipe tiket (Reguler/Premium), Harga, dan Stok. Coba klik **"Hapus"** pada salah satu baris tiket untuk mendemonstrasikan penghapusan dinamis. Klik **"Simpan Event"**.
* **Narasi (Voice Over):**
  > "Sekarang kita akan membuat event baru. Kita mengklik tombol '+ Tambah Event Baru'. Di sini terdapat form informasi dasar event. Kami juga mengintegrasikan fitur Cropper JS sehingga admin bisa memotong gambar poster langsung dengan rasio 16:9 yang rapi sebelum diunggah.
  > Yang menarik adalah bagian tiket event. Kita menggunakan form dinamis berbasis JavaScript. Admin dapat menambahkan opsi tipe tiket baru seperti tiket Reguler atau Premium lengkap dengan stok dan harga masing-masing. Jika terjadi kesalahan input, baris tiket tersebut juga bisa dihapus seketika sebelum disimpan ke database menggunakan Laravel DB Transaction."
* **Notice Kode yang Ditampilkan (di layar/editor):**
  * Tunjukkan script penambahan baris tiket dinamis di [create.blade.php](file:///Applications/XAMPP/xamppfiles/htdocs/ticketing_app/resources/views/pages/admin/events/create.blade.php#L153-L210):
    ```javascript
    // Listener event click pada tombol "+ Tambah Tiket"
    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        // Membuat elemen div penampung baris tiket baru dengan styling Tailwind
        row.className = 'tiket-row bg-gray-50 p-4 rounded-lg border space-y-3';
        
        // Memasukkan template input HTML dengan index dinamis (tiketCount) agar data tiket terkirim sebagai array ke controller
        row.innerHTML = `
            <div class="flex justify-between items-center border-b pb-2">
                <span class="text-sm font-semibold text-gray-700 ticket-title">Tiket #${tiketCount + 1}</span>
                <button type="button" class="remove-btn text-red-500 hover:text-red-700 text-xs font-semibold">Hapus</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700">Tipe Tiket <span class="text-red-500">*</span></label>
                    <select name="tikets[${tiketCount}][tipe]" required class="w-full px-3 py-2 border rounded bg-white text-sm">
                        <option value="reguler">Reguler</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="tikets[${tiketCount}][harga]" min="0" required class="w-full px-3 py-2 border rounded text-sm" placeholder="Contoh: 150000">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700">Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="tikets[${tiketCount}][stok]" min="0" required class="w-full px-3 py-2 border rounded text-sm" placeholder="100">
                </div>
            </div>
        `;
        // Menyisipkan baris tiket baru ke dalam container tiket
        container.appendChild(row);
        tiketCount++; // Menginkremen indeks counter tiket
        updateRemoveButtons(); // Memperbarui visibilitas tombol hapus
    });
    ```
  * Tunjukkan logic penyimpanan dengan DB Transaction di [EventController.php](file:///Applications/XAMPP/xamppfiles/htdocs/ticketing_app/app/Http/Controllers/EventController.php#L133-L155):
    ```php
    // Menjalankan query penyimpanan dalam DB Transaction untuk memastikan integritas data.
    // Jika proses penyimpanan tiket gagal, pembuatan event secara otomatis akan dibatalkan (rollback).
    \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $imagePath) {
        // 1. Simpan informasi dasar event ke tabel 'events'
        $event = auth()->user()->events()->create([
            'judul' => $validated['judul'],
            'kategori_id' => $validated['kategori_id'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'tanggal_waktu' => $validated['tanggal_waktu'],
            'gambar' => $imagePath,
        ]);

        // 2. Lakukan looping pada array tiket yang dikirimkan oleh form dinamis
        foreach ($validated['tikets'] as $tiketData) {
            // Simpan setiap item tiket ke tabel 'tikets' yang berelasi dengan id event ini
            $event->tikets()->create([
                'tipe' => $tiketData['tipe'],
                'harga' => $tiketData['harga'],
                'stok' => $tiketData['stok'],
            ]);
        }

        // 3. Catat riwayat status awal event ke tabel history status
        EventStatusHistory::create([
            'event_id' => $event->id,
            'status' => $event->status
        ]);
    });
    ```

---

### Adegan 4: Edit Event & Validasi Proteksi Tiket Terjual - Update (3:00 - 4:10)
* **Visual:** Di tabel event, klik tombol **"Edit"** pada salah satu event yang baru dibuat atau yang memiliki penjualan tiket. Perlihatkan perbedaan UI saat mengedit event dengan penjualan tiket (ada pesan peringatan kuning, dan kolom tanggal & waktu serta detail tiket dinonaktifkan/readonly). Coba edit deskripsi/judul event lalu simpan.
* **Narasi (Voice Over):**
  > "Untuk mengubah data event, kita cukup mengklik aksi 'Edit'. Aplikasi ini dilengkapi sistem validasi yang ketat. Jika tiket dari sebuah event telah terjual, aplikasi akan otomatis memproteksi data penting seperti tanggal & waktu event serta informasi tiket agar tidak dapat dimodifikasi demi menjaga konsistensi transaksi pembeli. Namun jika belum ada penjualan, admin bebas memperbarui seluruh data event dan susunan tiketnya secara dinamis."
* **Notice Kode yang Ditampilkan (di layar/editor):**
  * Tunjukkan pengecekan `hasSales` di [EventController.php](file:///Applications/XAMPP/xamppfiles/htdocs/ticketing_app/app/Http/Controllers/EventController.php#L205-L213):
    ```php
    // Mengamankan data jika event sudah memiliki catatan penjualan tiket (hasSales)
    if ($event->hasSales()) {
        $requestTime = Carbon::parse($request->tanggal_waktu)->format('Y-m-d H:i');
        $eventTime = $event->tanggal_waktu->format('Y-m-d H:i');
        
        // Membandingkan apakah admin mencoba memodifikasi tanggal dan waktu event
        if ($requestTime !== $eventTime) {
            // Mengembalikan respon error karena tanggal & waktu dilarang diubah setelah tiket terjual
            return back()->withErrors(['tanggal_waktu' => 'Tanggal dan waktu tidak dapat diubah karena tiket sudah terjual.'])->withInput();
        }
        // Menghapus field tanggal_waktu dari payload agar tidak terikut dalam query update database
        unset($validated['tanggal_waktu']);
    }
    ```

---

### Adegan 5: Duplikasi (Clone) & Hapus Event - Delete (4:10 - 4:45)
* **Visual:** 
  1. Klik tombol **"Clone"** di kolom aksi untuk menduplikasi event. Tunjukkan event duplikat muncul di daftar dengan judul tambahan `(Copy)`.
  2. Tunjukkan proses penghapusan: klik tombol **"Hapus"** pada satu event, lalu klik OK pada dialog konfirmasi.
  3. Tunjukkan fitur **Bulk Delete**: centang beberapa kotak pilihan (checkbox) di baris event, tombol merah **"Hapus Terpilih"** di atas tabel akan aktif, klik tombol tersebut dan konfirmasi untuk menghapus banyak event sekaligus.
* **Narasi (Voice Over):**
  > "Fitur tambahan yang kami sediakan adalah fitur 'Clone' untuk menduplikasi data event beserta jenis tiketnya secara cepat. Dan terakhir, untuk penghapusan data, admin dapat menghapus event secara satu per satu dengan konfirmasi keamanan, atau menggunakan fitur 'Bulk Delete' dengan mencentang beberapa event sekaligus untuk dihapus secara massal dalam satu klik."
* **Notice Kode yang Ditampilkan (di layar/editor):**
  * Tunjukkan kode Bulk Delete di [EventController.php](file:///Applications/XAMPP/xamppfiles/htdocs/ticketing_app/app/Http/Controllers/EventController.php#L64-L84):
    ```php
    public function bulkDelete(Request $request)
    {
        // Mengambil kumpulan ID event yang dicentang oleh admin
        $ids = $request->input('ids');
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada event yang dipilih.');
        }

        // Mengambil semua data event terpilih yang merupakan milik admin yang sedang login
        $events = auth()->user()->events()->whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($events as $event) {
            // Validasi: Event hanya boleh dihapus jika belum ada transaksi penjualan tiket
            if (!$event->hasSales()) {
                // Menghapus gambar poster dari disk storage jika filenya ada
                if (Storage::disk('public')->exists($event->gambar)) {
                    Storage::disk('public')->delete($event->gambar);
                }
                $event->delete(); // Menghapus baris record event di database
                $deletedCount++;
            }
        }

        // Memberikan feedback sukses kepada admin berisi jumlah event yang berhasil dihapus
        return redirect()->back()->with('success', "$deletedCount event berhasil dihapus secara massal.");
    }
    ```

---

### Adegan 6: Penutup (4:45 - 5:00)
* **Visual:** Kembali ke tampilan dashboard admin utama atau halaman utama aplikasi web ticketing. Tampilkan teks penutup.
* **Narasi (Voice Over):**
  > "Itulah demonstrasi lengkap mengenai manajemen event pada Ticketing App kita. Seluruh proses Create, Read, Update, Delete, filter pencarian dinamis, hingga validasi tiket terintegrasi dengan baik untuk kemudahan operasional admin. Terima kasih telah menonton!"
