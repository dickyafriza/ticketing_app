<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'tanggal_waktu' => '2024-08-15 19:00:00',
                'lokasi' => 'Stadion Utama',
                'kategori_id' => 1,
                'gambar' => 'konser_rock.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Pameran Seni Kontemporer',
                'deskripsi' => 'Jelajahi karya seni modern dari seniman lokal dan internasional.',
                'tanggal_waktu' => '2024-09-10 10:00:00',
                'lokasi' => 'Galeri Seni Kota',
                'kategori_id' => 2,
                'gambar' => 'pameran_seni.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Festival Makanan Internasional',
                'deskripsi' => 'Cicipi berbagai hidangan lezat dari seluruh dunia.',
                'tanggal_waktu' => '2024-10-05 12:00:00',
                'lokasi' => 'Taman Kota',
                'kategori_id' => 3,
                'gambar' => 'festival_makanan.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Festival Film Indie',
                'deskripsi' => 'Penayangan film independen dari berbagai kreator lokal.',
                'tanggal_waktu' => '2024-11-20 18:00:00',
                'lokasi' => 'Galeri Seni Kota',
                'kategori_id' => 4, // Festival
                'gambar' => 'pameran_seni.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Pementasan Teater Klasik',
                'deskripsi' => 'Pertunjukan teater klasik karya penulis legendaris.',
                'tanggal_waktu' => '2024-12-01 19:30:00',
                'lokasi' => 'Stadion Utama',
                'kategori_id' => 5, // Pertunjukan Teater
                'gambar' => 'konser.jpg',
            ],
        ];
        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
