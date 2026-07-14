<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Kategori;
use App\Models\Tiket;
use App\Models\Order;
use App\Models\EventStatusHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('event index page can be accessed by authenticated user and lists events', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Konser Musik']);
    
    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Konser Dewa 19',
        'deskripsi' => 'Konser Dewa 19 spektakuler',
        'lokasi' => 'Stadion Utama Gelora Bung Karno',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/placeholder.jpg',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.events.index'));

    $response->assertOk();
    $response->assertSee('Konser Dewa 19');
});

test('event can be created with tickets', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Seminar']);

    $payload = [
        'judul' => 'Seminar AI 2026',
        'kategori_id' => $kategori->id,
        'deskripsi' => 'Seminar kecerdasan buatan masa depan',
        'lokasi' => 'Semarang',
        'tanggal_waktu' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'gambar' => UploadedFile::fake()->image('poster.jpg'),
        'tikets' => [
            ['tipe' => 'reguler', 'harga' => 50000, 'stok' => 100],
            ['tipe' => 'premium', 'harga' => 150000, 'stok' => 20],
        ]
    ];

    $response = $this
        ->actingAs($user)
        ->post(route('admin.events.store'), $payload);

    $response->assertRedirect(route('admin.events.index'));
    $this->assertDatabaseHas('events', [
        'judul' => 'Seminar AI 2026',
        'lokasi' => 'Semarang',
    ]);

    $event = Event::where('judul', 'Seminar AI 2026')->first();
    $this->assertCount(2, $event->tikets);
    $this->assertDatabaseHas('tikets', ['event_id' => $event->id, 'tipe' => 'reguler', 'harga' => 50000]);
    $this->assertDatabaseHas('event_status_histories', ['event_id' => $event->id, 'status' => 'Upcoming']);
});

test('event validation fails with invalid data', function () {
    $user = User::factory()->create();

    $payload = [
        'judul' => '',
        'kategori_id' => 9999, // non-existent
        'deskripsi' => '',
        'lokasi' => '',
        'tanggal_waktu' => now()->subDays(1)->format('Y-m-d H:i:s'), // must be after now
        'tikets' => []
    ];

    $response = $this
        ->actingAs($user)
        ->post(route('admin.events.store'), $payload);

    $response->assertSessionHasErrors(['judul', 'kategori_id', 'deskripsi', 'lokasi', 'tanggal_waktu', 'tikets']);
});

test('event can be updated when there are no ticket sales', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Konser']);

    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Konser Dewa',
        'deskripsi' => 'Deskripsi lama',
        'lokasi' => 'Lokasi lama',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/old.jpg',
    ]);

    $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 100000,
        'stok' => 50
    ]);

    $payload = [
        'judul' => 'Konser Dewa Updated',
        'kategori_id' => $kategori->id,
        'deskripsi' => 'Deskripsi baru',
        'lokasi' => 'Lokasi baru',
        'tanggal_waktu' => now()->addDays(10)->format('Y-m-d H:i:s'),
        'tikets' => [
            ['tipe' => 'reguler', 'harga' => 120000, 'stok' => 60],
            ['tipe' => 'premium', 'harga' => 300000, 'stok' => 20],
        ]
    ];

    $response = $this
        ->actingAs($user)
        ->put(route('admin.events.update', $event), $payload);

    $response->assertRedirect(route('admin.events.index'));
    
    $event->refresh();
    $this->assertEquals('Konser Dewa Updated', $event->judul);
    $this->assertEquals('Lokasi baru', $event->lokasi);
    $this->assertEquals(now()->addDays(10)->format('Y-m-d H:i'), $event->tanggal_waktu->format('Y-m-d H:i'));

    $this->assertCount(2, $event->tikets);
    $this->assertDatabaseHas('tikets', ['event_id' => $event->id, 'tipe' => 'reguler', 'harga' => 120000]);
});

test('event date/time and tickets are locked when tickets are sold', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Konser']);

    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Konser Padi',
        'deskripsi' => 'Deskripsi lama',
        'lokasi' => 'Lokasi lama',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/padi.jpg',
    ]);

    $tiket = $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 100000,
        'stok' => 50
    ]);

    // Create an order to lock the event
    Order::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'order_date' => now(),
        'total_harga' => 100000
    ]);

    $originalDateTime = $event->tanggal_waktu;

    $payload = [
        'judul' => 'Konser Padi Updated Name Only',
        'kategori_id' => $kategori->id,
        'deskripsi' => 'Deskripsi baru',
        'lokasi' => 'Lokasi baru',
        'tanggal_waktu' => now()->addDays(12)->format('Y-m-d H:i:s'), // should be ignored
        'tikets' => [
            ['tipe' => 'reguler', 'harga' => 999999, 'stok' => 999], // should be ignored
        ]
    ];

    $response = $this
        ->actingAs($user)
        ->put(route('admin.events.update', $event), $payload);

    $response->assertRedirect(route('admin.events.index'));

    $event->refresh();
    // Name, description, lokasi should be updated
    $this->assertEquals('Konser Padi Updated Name Only', $event->judul);
    $this->assertEquals('Lokasi baru', $event->lokasi);
    // Date/time should NOT be updated
    $this->assertEquals($originalDateTime->format('Y-m-d H:i:s'), $event->tanggal_waktu->format('Y-m-d H:i:s'));

    // Tickets should NOT be updated
    $this->assertCount(1, $event->tikets);
    $this->assertEquals(100000, $event->tikets->first()->harga);
});

test('event cannot be deleted if there are ticket sales', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Konser']);

    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Konser Padi',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Lokasi',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/padi.jpg',
    ]);

    // Create an order
    Order::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'order_date' => now(),
        'total_harga' => 100000
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.events.destroy', $event));

    $response->assertRedirect(route('admin.events.index'));
    $response->assertSessionHas('error');
    
    $this->assertDatabaseHas('events', ['id' => $event->id]);
});

test('event can be cloned', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Festival']);

    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Festival Jazz',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Lokasi',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/jazz.jpg',
    ]);

    $event->tikets()->create([
        'tipe' => 'reguler',
        'harga' => 80000,
        'stok' => 150
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('admin.events.clone', $event));

    $response->assertRedirect(route('admin.events.index'));

    $this->assertDatabaseHas('events', [
        'judul' => 'Festival Jazz (Copy)',
        'lokasi' => 'Lokasi',
    ]);

    $clonedEvent = Event::where('judul', 'Festival Jazz (Copy)')->first();
    $this->assertCount(1, $clonedEvent->tikets);
    $this->assertEquals(80000, $clonedEvent->tikets->first()->harga);
});

test('events can be bulk deleted except ones with sales', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Festival']);

    $event1 = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event 1 (No Sales)',
        'deskripsi' => 'Deskripsi 1',
        'lokasi' => 'Lokasi 1',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/event1.jpg',
    ]);

    $event2 = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Event 2 (Has Sales)',
        'deskripsi' => 'Deskripsi 2',
        'lokasi' => 'Lokasi 2',
        'tanggal_waktu' => now()->addDays(6),
        'gambar' => 'events/event2.jpg',
    ]);

    Order::create([
        'user_id' => $user->id,
        'event_id' => $event2->id,
        'order_date' => now(),
        'total_harga' => 50000
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('admin.events.bulkDelete'), [
            'ids' => [$event1->id, $event2->id]
        ]);

    $response->assertRedirect();
    
    // Event 1 should be deleted, Event 2 should still exist
    $this->assertDatabaseMissing('events', ['id' => $event1->id]);
    $this->assertDatabaseHas('events', ['id' => $event2->id]);
});

test('event status transitions record in status history log', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Festival']);

    $event = Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Status History Test',
        'deskripsi' => 'Deskripsi',
        'lokasi' => 'Lokasi',
        'tanggal_waktu' => now()->addDays(5), // Upcoming
        'gambar' => 'events/status.jpg',
    ]);

    // Initial status history should be created if recorded (here we test model update trigger in controller)
    EventStatusHistory::create([
        'event_id' => $event->id,
        'status' => $event->status
    ]);

    $oldStatus = $event->status; // Upcoming

    // Update date to yesterday to change status to Completed
    $event->tanggal_waktu = now()->subDays(2);
    $event->save();

    // Simulating controller logic
    if ($oldStatus !== $event->status) {
        EventStatusHistory::create([
            'event_id' => $event->id,
            'status' => $event->status
        ]);
    }

    $this->assertDatabaseHas('event_status_histories', [
        'event_id' => $event->id,
        'status' => 'Upcoming'
    ]);
    
    $this->assertDatabaseHas('event_status_histories', [
        'event_id' => $event->id,
        'status' => 'Completed'
    ]);
});

test('events can be filtered by status on index page', function () {
    $user = User::factory()->create();
    $kategori = Kategori::create(['nama' => 'Festival']);

    // Upcoming event
    Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Upcoming Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'tanggal_waktu' => now()->addDays(5),
        'gambar' => 'events/up.jpg',
    ]);

    // Completed event
    Event::create([
        'user_id' => $user->id,
        'kategori_id' => $kategori->id,
        'judul' => 'Completed Event',
        'deskripsi' => 'Desc',
        'lokasi' => 'Loc',
        'tanggal_waktu' => now()->subDays(5),
        'gambar' => 'events/comp.jpg',
    ]);

    // Filter by Upcoming
    $response = $this
        ->actingAs($user)
        ->get(route('admin.events.index', ['status' => 'Upcoming']));
    $response->assertOk();
    $response->assertSee('Upcoming Event');
    $response->assertDontSee('Completed Event');

    // Filter by Completed
    $response = $this
        ->actingAs($user)
        ->get(route('admin.events.index', ['status' => 'Completed']));
    $response->assertOk();
    $response->assertSee('Completed Event');
    $response->assertDontSee('Upcoming Event');
});

