@extends('layouts.admin_layouts')

@section('title', 'Tambah Event Baru')

@section('content')
<!-- Include Cropper.js -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Tambah Event Baru</h2>
        <a href="{{ route('admin.events.index') }}" class="text-gray-600 hover:text-gray-900">
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

    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" id="event-form">
        @csrf

        <!-- Informasi Event -->
        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Informasi Event</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Event *</label>
                <input type="text" name="judul" value="{{ old('judul') }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                <select name="kategori_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
                <textarea name="deskripsi" rows="4" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi') }}</textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi *</label>
                <input type="text" name="lokasi" value="{{ old('lokasi') }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal & Waktu *</label>
                <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu') }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Poster *</label>
                <input type="file" name="gambar" id="imageInput" accept="image/*" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, max 2MB. Gambar dapat di-crop sebelum diunggah.</p>
                
                <!-- Crop Preview Container (Hidden by default) -->
                <div id="crop-container" class="hidden mt-4 bg-gray-50 p-4 rounded-lg border">
                    <h4 class="text-sm font-medium mb-2">Potong Gambar</h4>
                    <div style="max-height: 400px; overflow: hidden;">
                        <img id="imageToCrop" src="" class="max-w-full">
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="button" id="btnCrop" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Gunakan Potongan Ini</button>
                        <button type="button" id="btnCancelCrop" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">Batal</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tiket Event -->
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-lg font-medium text-gray-900">Tiket Event *</h3>
            <button type="button" id="add-tiket-btn" class="text-sm px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">
                + Tambah Tiket
            </button>
        </div>
        
        <div id="tiket-container" class="space-y-4 mb-8">
            <div class="tiket-row flex gap-4 items-end bg-gray-50 p-4 rounded-lg">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Tiket</label>
                    <select name="tikets[0][tipe]" required class="w-full px-3 py-2 border rounded">
                        <option value="reguler">Reguler</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" name="tikets[0][harga]" min="0" required class="w-full px-3 py-2 border rounded" placeholder="Contoh: 150000">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Stok</label>
                    <input type="number" name="tikets[0][stok]" min="1" required class="w-full px-3 py-2 border rounded" placeholder="100">
                </div>
                <div class="w-10"></div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Reset</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Event</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ticket Logic
        const container = document.getElementById('tiket-container');
        const addBtn = document.getElementById('add-tiket-btn');
        let tiketCount = 1;

        addBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'tiket-row flex gap-4 items-end bg-gray-50 p-4 rounded-lg';
            row.innerHTML = `
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Tiket</label>
                    <select name="tikets[${tiketCount}][tipe]" required class="w-full px-3 py-2 border rounded">
                        <option value="reguler">Reguler</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" name="tikets[${tiketCount}][harga]" min="0" required class="w-full px-3 py-2 border rounded" placeholder="Contoh: 150000">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Stok</label>
                    <input type="number" name="tikets[${tiketCount}][stok]" min="1" required class="w-full px-3 py-2 border rounded" placeholder="100">
                </div>
                <div class="w-10 flex justify-center">
                    <button type="button" class="remove-btn text-red-500 hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;
            container.appendChild(row);
            tiketCount++;
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn')) {
                e.target.closest('.tiket-row').remove();
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
            imageInput.value = ''; // clear input
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        btnCrop.addEventListener('click', function() {
            if (cropper) {
                cropper.getCroppedCanvas().toBlob((blob) => {
                    // Buat file baru dari blob
                    const croppedFile = new File([blob], originalFileName, { type: "image/jpeg" });
                    
                    // Masukkan ke input file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    imageInput.files = dataTransfer.files;
                    
                    // Sembunyikan cropper
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
