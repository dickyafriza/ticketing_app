@extends('layouts.admin_layouts')

@section('title', 'Management Lokasi')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Lokasi</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.lokasi.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                + Tambah Lokasi
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter and Search -->
    <form action="{{ route('admin.lokasi.index') }}" method="GET" class="mb-6 flex gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama lokasi..." class="px-4 py-2 border rounded-lg flex-1">
        
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">Cari</button>
        <a href="{{ route('admin.lokasi.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Reset</a>
    </form>

    <!-- Lokasi Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Nama Lokasi</th>
                    <th scope="col" class="px-6 py-3">Aktif</th>
                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lokasis as $lokasi)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $lokasi->id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $lokasi->nama_lokasi }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $lokasi->aktif == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $lokasi->aktif }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.lokasi.edit', $lokasi->id) }}" class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>

                        <!-- Delete Form -->
                        <form action="{{ route('admin.lokasi.destroy', $lokasi->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus lokasi ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="this.closest('form').submit()" class="text-red-600 hover:text-red-900">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data lokasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $lokasis->links() }}
    </div>
</div>
@endsection
