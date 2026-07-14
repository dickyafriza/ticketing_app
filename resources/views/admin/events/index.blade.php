@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Event</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.events.export') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                Export ke Excel
            </a>
            <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                + Tambah Event Baru
            </a>
        </div>
    </div>

    @if (session('error'))
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter and Search -->
    <form action="{{ route('admin.events.index') }}" method="GET" class="mb-6 flex gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul event..." class="px-4 py-2 border rounded-lg flex-1">
        
        <select name="kategori" class="px-4 py-2 border rounded-lg">
            <option value="">Semua Kategori</option>
            @foreach($kategoris as $kategori)
                <option value="{{ $kategori->id }}" {{ request('kategori') == $kategori->id ? 'selected' : '' }}>
                    {{ $kategori->nama }}
                </option>
            @endforeach
        </select>
        
        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="">Semua Status</option>
            <option value="Upcoming" {{ request('status') === 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
            <option value="Ongoing" {{ request('status') === 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
            <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
        </select>
        
        <select name="sort" class="px-4 py-2 border rounded-lg">
            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
        </select>
        
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">Filter</button>
        <a href="{{ route('admin.events.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Reset</a>
    </form>

    <!-- Bulk Delete Form Wrapper -->
    <form action="{{ route('admin.events.bulkDelete') }}" method="POST" id="bulk-delete-form">
        @csrf
        
        <div class="mb-4">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm disabled:opacity-50" id="btn-bulk-delete" disabled onclick="return confirm('Yakin ingin menghapus event yang dipilih?');">
                Hapus Terpilih
            </button>
        </div>

        <!-- Events Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-4">
                            <input type="checkbox" id="check-all" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </th>
                        <th scope="col" class="px-6 py-3">Event</th>
                        <th scope="col" class="px-6 py-3">Kategori</th>
                        <th scope="col" class="px-6 py-3">Tanggal & Waktu</th>
                        <th scope="col" class="px-6 py-3">Lokasi</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $event->id }}" class="check-item rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 flex items-center gap-3">
                            <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="w-10 h-10 object-cover rounded">
                            {{ $event->judul }}
                        </td>
                        <td class="px-6 py-4">{{ $event->kategori->nama }}</td>
                        <td class="px-6 py-4">{{ $event->tanggal_waktu->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4">{{ $event->lokasi }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium 
                                {{ $event->status === 'Upcoming' ? 'bg-green-100 text-green-800' : 
                                   ($event->status === 'Ongoing' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $event->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.events.edit', $event->id) }}" class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                            
                            <!-- Clone Form -->
                            <form action="{{ route('admin.events.clone', $event->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menduplikasi event ini?');">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 mr-2">Clone</button>
                            </form>

                            <!-- Delete Form -->
                            <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus event ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="this.closest('form').submit()" class="text-red-600 hover:text-red-900">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada event ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $events->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all');
        const checkItems = document.querySelectorAll('.check-item');
        const btnBulkDelete = document.getElementById('btn-bulk-delete');

        function updateBulkDeleteBtn() {
            const checkedCount = document.querySelectorAll('.check-item:checked').length;
            btnBulkDelete.disabled = checkedCount === 0;
        }

        checkAll.addEventListener('change', function() {
            checkItems.forEach(item => {
                item.checked = checkAll.checked;
            });
            updateBulkDeleteBtn();
        });

        checkItems.forEach(item => {
            item.addEventListener('change', function() {
                const allChecked = document.querySelectorAll('.check-item:checked').length === checkItems.length;
                checkAll.checked = allChecked;
                updateBulkDeleteBtn();
            });
        });
    });
</script>
@endsection
