<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $lokasis = Lokasi::when($search, function ($query, $search) {
                return $query->where('nama_lokasi', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('pages.admin.lokasi.index', compact('lokasis'));
    }

    public function create()
    {
        return view('pages.admin.lokasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'aktif' => 'required|in:Y,N',
        ]);

        Lokasi::create($request->all());

        return redirect()->route('admin.lokasi.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Lokasi $lokasi)
    {
        return view('pages.admin.lokasi.edit', compact('lokasi'));
    }

    public function update(Request $request, Lokasi $lokasi)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'aktif' => 'required|in:Y,N',
        ]);

        $lokasi->update($request->all());

        return redirect()->route('admin.lokasi.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Lokasi $lokasi)
    {
        $lokasi->delete();
        return redirect()->route('admin.lokasi.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}
