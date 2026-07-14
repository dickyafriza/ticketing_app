<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use App\Models\EventStatusHistory;
use App\Http\Requests\EventFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventsExport;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->events()->with('kategori');

        if ($request->has('search') && $request->search != '') {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_id', $request->kategori);
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'Upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'Ongoing') {
                $query->ongoing();
            } elseif ($request->status === 'Completed') {
                $query->completed();
            }
        }

        if ($request->has('sort') && $request->sort == 'oldest') {
            $query->orderBy('tanggal_waktu', 'asc');
        } else {
            $query->orderBy('tanggal_waktu', 'desc');
        }

        $events = $query->paginate(10);
        $kategoris = Kategori::all();

        return view('admin.events.index', compact('events', 'kategoris'));
    }

    public function export()
    {
        return Excel::download(new EventsExport, 'events.xlsx');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada event yang dipilih.');
        }

        $events = auth()->user()->events()->whereIn('id', $ids)->get();
        $deletedCount = 0;
        foreach ($events as $event) {
            if (!$event->hasSales()) {
                if (Storage::disk('public')->exists($event->gambar)) {
                    Storage::disk('public')->delete($event->gambar);
                }
                $event->delete();
                $deletedCount++;
            }
        }

        return redirect()->back()->with('success', "$deletedCount event berhasil dihapus secara massal.");
    }

    public function clone(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($event, &$newEvent) {
            $newEvent = $event->replicate();
            $newEvent->judul = $event->judul . ' (Copy)';
            $newEvent->save();

            foreach ($event->tikets as $tiket) {
                $newTiket = $tiket->replicate();
                $newTiket->event_id = $newEvent->id;
                $newTiket->save();
            }

            EventStatusHistory::create([
                'event_id' => $newEvent->id,
                'status' => $newEvent->status
            ]);
        });

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diduplikasi.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = Kategori::all();
        return view('admin.events.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventFormRequest $request)
    {
        $validated = $request->validated();
        
        $imagePath = $request->file('gambar')->store('events', 'public');

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $imagePath) {
            $event = auth()->user()->events()->create([
                'judul' => $validated['judul'],
                'kategori_id' => $validated['kategori_id'],
                'deskripsi' => $validated['deskripsi'],
                'lokasi' => $validated['lokasi'],
                'tanggal_waktu' => $validated['tanggal_waktu'],
                'gambar' => $imagePath,
            ]);

            foreach ($validated['tikets'] as $tiketData) {
                $event->tikets()->create([
                    'tipe' => $tiketData['tipe'],
                    'harga' => $tiketData['harga'],
                    'stok' => $tiketData['stok'],
                ]);
            }

            EventStatusHistory::create([
                'event_id' => $event->id,
                'status' => $event->status
            ]);
        });

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load(['kategori', 'tikets']);
        $relatedEvents = Event::where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->upcoming()
            ->take(4)
            ->get();

        return view('events.show', [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $event->load('tikets');
        $kategoris = Kategori::all();
        return view('admin.events.edit', compact('event', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventFormRequest $request, Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();
        
        // Cek jika sudah ada penjualan
        if ($event->hasSales()) {
            // Jangan update tanggal_waktu
            unset($validated['tanggal_waktu']);
        }

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if (Storage::disk('public')->exists($event->gambar)) {
                Storage::disk('public')->delete($event->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('events', 'public');
        }

        $oldStatus = $event->status;

        \Illuminate\Support\Facades\DB::transaction(function () use ($event, $validated, $oldStatus, $request) {
            $event->update($validated);

            if ($oldStatus !== $event->status) {
                EventStatusHistory::create([
                    'event_id' => $event->id,
                    'status' => $event->status
                ]);
            }

            // Update Tiket (Sederhananya hapus yang lama dan buat baru jika belum ada penjualan)
            if (!$event->hasSales()) {
                $event->tikets()->delete();
                foreach ($request->tikets as $tiketData) {
                    $event->tikets()->create([
                        'tipe' => $tiketData['tipe'],
                        'harga' => $tiketData['harga'],
                        'stok' => $tiketData['stok'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        if ($event->hasSales()) {
            return redirect()->route('admin.events.index')->with('error', 'Tidak dapat menghapus event yang sudah memiliki penjualan tiket.');
        }

        if (Storage::disk('public')->exists($event->gambar)) {
            Storage::disk('public')->delete($event->gambar);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }
}