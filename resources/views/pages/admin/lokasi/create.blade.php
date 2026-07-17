@extends('layouts.admin_layouts')

@section('title', 'Tambah Lokasi')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Tambah Lokasi Baru</h2>
        <a href="{{ route('admin.lokasi.index') }}" class="text-gray-600 hover:text-gray-900">
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

    <form action="{{ route('admin.lokasi.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="nama_lokasi" class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
            <input type="text" name="nama_lokasi" id="nama_lokasi" value="{{ old('nama_lokasi') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" required>
        </div>

        <div class="mb-6">
            <label for="aktif" class="block text-sm font-medium text-gray-700 mb-1">Status Aktif</label>
            <select name="aktif" id="aktif" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" required>
                <option value="Y" {{ old('aktif') == 'Y' ? 'selected' : '' }}>Y - Aktif</option>
                <option value="N" {{ old('aktif') == 'N' ? 'selected' : '' }}>N - Tidak Aktif</option>
            </select>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.lokasi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Lokasi</button>
        </div>
    </form>
</div>
@endsection
