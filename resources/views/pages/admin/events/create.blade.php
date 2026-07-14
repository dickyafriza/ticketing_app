@extends('layouts.admin_layouts')

@section('title', 'Tambah Event Baru')

@section('content')
<!-- Include Cropper.js -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Tambah Event Baru</h2>
        <a href="{{ route('admin.events.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center gap-1">
            &larr; Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" id="event-form" class="space-y-6">
        @csrf

        <!-- Informasi Event (Grid 2 columns) -->
        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Informasi Event</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Judul Event -->
            <div class="space-y-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Judul Event</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" name="judul" value="{{ old('judul') }}" required class="input input-bordered w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            
            <!-- Kategori -->
            <div class="space-y-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Kategori</span>
                    <span class="text-red-500">*</span>
                </label>
                <select name="kategori_id" required class="select select-bordered w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Lokasi -->
            <div class="space-y-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Lokasi</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" name="lokasi" value="{{ old('lokasi') }}" required class="input input-bordered w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            
            <!-- Tanggal & Waktu -->
            <div class="space-y-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu') }}" required class="input input-bordered w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            
            <!-- Gambar File Input -->
            <div class="md:col-span-2 space-y-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Gambar Poster</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="file" name="gambar" id="imageInput" accept="image/*" required class="file-input file-input-bordered w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                <p class="text-xs text-gray-500">Format: JPG, JPEG, PNG, max 2MB. Gambar dapat di-crop berasio 16:9 sebelum diunggah.</p>
                
                <!-- Crop Preview Container (Hidden by default) -->
                <div id="crop-container" class="hidden mt-4 bg-gray-50 p-4 rounded-lg border">
                    <h4 class="text-sm font-medium mb-2 text-gray-700">Potong Gambar</h4>
                    <div style="max-height: 400px; overflow: hidden;">
                        <img id="imageToCrop" src="" class="max-w-full">
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="button" id="btnCrop" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium">Gunakan Potongan Ini</button>
                        <button type="button" id="btnCancelCrop" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm font-medium">Batal</button>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Textarea (span 2 columns) -->
            <div class="md:col-span-2 space-y-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Deskripsi</span>
                    <span class="text-red-500">*</span>
                </label>
                <textarea name="deskripsi" rows="4" required class="textarea textarea-bordered w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('deskripsi') }}</textarea>
            </div>
        </div>

        <!-- Tiket Event -->
        <div class="flex justify-between items-center border-b pb-2 pt-4">
            <h3 class="text-lg font-medium text-gray-900">Tiket Event</h3>
            <button type="button" id="add-tiket-btn" class="text-sm px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 font-medium">
                + Tambah Tiket
            </button>
        </div>
        
        <!-- Container untuk ticket cards -->
        <div id="tiket-container" class="space-y-4">
            <!-- Ticket Card 1 (Default) -->
            <div class="tiket-row bg-gray-50 p-4 rounded-lg border space-y-3">
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-sm font-semibold text-gray-700 ticket-title">Tiket #1</span>
                    <button type="button" class="remove-btn text-red-500 hover:text-red-700 text-xs font-semibold hidden">Hapus</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Tipe Tiket <span class="text-red-500">*</span></label>
                        <select name="tikets[0][tipe]" required class="w-full px-3 py-2 border rounded bg-white text-sm">
                            <option value="reguler">Reguler</option>
                            <option value="premium">Premium</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="tikets[0][harga]" min="0" required class="w-full px-3 py-2 border rounded text-sm" placeholder="Contoh: 150000">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-gray-700">Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="tikets[0][stok]" min="0" required class="w-full px-3 py-2 border rounded text-sm" placeholder="100">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4 border-t pt-4">
            <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">Reset</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Simpan Event</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('tiket-container');
        const addBtn = document.getElementById('add-tiket-btn');
        let tiketCount = 1;

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.tiket-row');
            rows.forEach((row, index) => {
                const title = row.querySelector('.ticket-title');
                title.textContent = `Tiket #${index + 1}`;
                
                const removeBtn = row.querySelector('.remove-btn');
                if (rows.length > 1) {
                    removeBtn.classList.remove('hidden');
                } else {
                    removeBtn.classList.add('hidden');
                }
            });
        }

        addBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'tiket-row bg-gray-50 p-4 rounded-lg border space-y-3';
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
            container.appendChild(row);
            tiketCount++;
            updateRemoveButtons();
        });

        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-btn')) {
                e.target.closest('.tiket-row').remove();
                updateRemoveButtons();
            }
        });

        // Cropper Logic
        const imageInput = document.getElementById('imageInput');
        const imageToCrop = document.getElementById('imageToCrop');
        const cropContainer = document.getElementById('crop-container');
        const btnCrop = document.getElementById('btnCrop');
        const btnCancelCrop = document.getElementById('btnCancelCrop');
        let cropper;
        let originalFileName = "";

        imageInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                originalFileName = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    imageToCrop.src = e.target.result;
                    cropContainer.classList.remove('hidden');
                    
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 16 / 9,
                        viewMode: 2,
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        btnCancelCrop.addEventListener('click', function() {
            cropContainer.classList.add('hidden');
            imageInput.value = '';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        btnCrop.addEventListener('click', function() {
            if (cropper) {
                cropper.getCroppedCanvas().toBlob((blob) => {
                    const croppedFile = new File([blob], originalFileName, { type: "image/jpeg" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    imageInput.files = dataTransfer.files;
                    
                    cropContainer.classList.add('hidden');
                    cropper.destroy();
                    cropper = null;
                    
                    alert("Gambar berhasil dipotong dan siap diunggah!");
                }, 'image/jpeg');
            }
        });
    });
</script>
@endsection
