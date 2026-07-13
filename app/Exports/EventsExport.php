<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return auth()->user()->events()->with(['kategori', 'orders'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Judul Event',
            'Kategori',
            'Tanggal & Waktu',
            'Lokasi',
            'Status',
            'Total Penjualan Tiket',
        ];
    }

    public function map($event): array
    {
        return [
            $event->id,
            $event->judul,
            $event->kategori ? $event->kategori->nama : '-',
            $event->tanggal_waktu->format('Y-m-d H:i'),
            $event->lokasi,
            $event->status,
            $event->orders()->where('status', 'paid')->count(),
        ];
    }
}
