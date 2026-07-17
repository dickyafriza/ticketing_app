<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'user_id' => 1,
                'judul' => 'Konser Musik Rock',
                'deskripsi' => 'Nikmati malam penuh energi dengan band rock terkenal.',
                'tanggal_waktu' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'), // Upcoming
                'lokasi' => 'Stadion Besar',
                'kategori_id' => 1,
                'gambar' => 'konser_rock.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Pameran Seni Kontemporer',
                'deskripsi' => 'Jelajahi karya seni modern dari seniman lokal dan internasional.',
                'tanggal_waktu' => Carbon::now()->subHours(1)->format('Y-m-d H:i:s'), // Ongoing (dimulai 1 jam lalu, durasi 3 jam)
                'lokasi' => 'Galeri Seni Kota',
                'kategori_id' => 2,
                'gambar' => 'pameran_seni.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Festival Makanan Internasional',
                'deskripsi' => 'Cicipi berbagai hidangan lezat dari seluruh dunia.',
                'tanggal_waktu' => Carbon::now()->addDays(12)->format('Y-m-d H:i:s'), // Upcoming
                'lokasi' => 'Taman Kota',
                'kategori_id' => 3,
                'gambar' => 'festival_makanan.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Festival Film Indie',
                'deskripsi' => 'Penayangan film independen dari berbagai kreator lokal.',
                'tanggal_waktu' => Carbon::now()->subHours(2)->format('Y-m-d H:i:s'), // Ongoing (dimulai 2 jam lalu, durasi 3 jam)
                'lokasi' => 'Galeri Seni Kota',
                'kategori_id' => 4,
                'gambar' => 'pameran_seni.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Pementasan Teater Klasik',
                'deskripsi' => 'Pertunjukan teater klasik karya penulis legendaris.',
                'tanggal_waktu' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'), // Completed (sudah lampau)
                'lokasi' => 'Stadion Besar',
                'kategori_id' => 5,
                'gambar' => 'konser.jpg',
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
