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
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */ // *2*
    public function index(Request $request)
    {
        $query = auth()->user()->events()->with(['kategori', 'tikets']);

        // Search by judul or lokasi
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhere('lokasi', 'like', '%' . $search . '%');
            });
        }

        // Filter by kategori_id
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_id', $request->kategori);
        }

        // Filter by status (Upcoming, Ongoing, Completed)
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'Upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'Ongoing') {
                $query->ongoing();
            } elseif ($request->status === 'Completed') {
                $query->completed();
            }
        }

        // Sort by tanggal_waktu (asc/desc), default asc
        $sort = $request->input('sort', 'asc');
        $query->orderBy('tanggal_waktu', $sort);

        $events = $query->paginate(10);
        $kategoris = Kategori::all();

        return view('pages.admin.events.index', compact('events', 'kategoris'));
    }

// *2*
    public function export()
    {
        return Excel::download(new EventsExport, 'events.xlsx');
    }
// *5*
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
// *5*

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = Kategori::all();
        return view('pages.admin.events.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventFormRequest $request)
    {
        $validated = $request->validated();
        
        $imagePath = 'konser.jpg';
        if ($request->hasFile('gambar')) {
            $imagePath = $request->file('gambar')->store('events', 'public');
        }
// *3*
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
// *3*
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
        $hasSales = $event->hasSales();
        return view('pages.admin.events.edit', compact('event', 'kategoris', 'hasSales'));
    }

    /**
     * Update the specified resource in storage.
     */ // *4*
    public function update(EventFormRequest $request, Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();
        
        // Jika event sudah terjual (hasSales()): Tampilkan error jika tanggal_waktu berubah
        if ($event->hasSales()) {
            $requestTime = Carbon::parse($request->tanggal_waktu)->format('Y-m-d H:i');
            $eventTime = $event->tanggal_waktu->format('Y-m-d H:i');
            if ($requestTime !== $eventTime) {
                return back()->withErrors(['tanggal_waktu' => 'Tanggal dan waktu tidak dapat diubah karena tiket sudah terjual.'])->withInput();
            }
            // Jangan update tanggal_waktu jika hasSales
            unset($validated['tanggal_waktu']);
        }

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika bukan default
            if ($event->gambar && $event->gambar !== 'konser.jpg' && Storage::disk('public')->exists($event->gambar)) {
                Storage::disk('public')->delete($event->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('events', 'public');
        }
// *4*
        $oldStatus = $event->status;

        \Illuminate\Support\Facades\DB::transaction(function () use ($event, $validated, $oldStatus, $request) {
            $event->update($validated);

            if ($oldStatus !== $event->status) {
                EventStatusHistory::create([
                    'event_id' => $event->id,
                    'status' => $event->status
                ]);
            }

            // Handle tickets
            if (!$event->hasSales()) {
                $ticketIdsInRequest = [];
                foreach ($request->tikets as $tiketData) {
                    if (!empty($tiketData['id'])) {
                        $ticketIdsInRequest[] = $tiketData['id'];
                    }
                }

                // Delete removed tickets (hanya jika belum ada penjualan)
                $event->tikets()->whereNotIn('id', $ticketIdsInRequest)->delete();

                foreach ($request->tikets as $tiketData) {
                    if (!empty($tiketData['id'])) {
                        // Update existing tickets
                        $event->tikets()->where('id', $tiketData['id'])->update([
                            'tipe' => $tiketData['tipe'],
                            'harga' => $tiketData['harga'],
                            'stok' => $tiketData['stok'],
                        ]);
                    } else {
                        // Create new tickets
                        $event->tikets()->create([
                            'tipe' => $tiketData['tipe'],
                            'harga' => $tiketData['harga'],
                            'stok' => $tiketData['stok'],
                        ]);
                    }
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

        if ($event->gambar && $event->gambar !== 'konser.jpg' && Storage::disk('public')->exists($event->gambar)) {
            Storage::disk('public')->delete($event->gambar);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }
}