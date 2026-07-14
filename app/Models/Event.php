<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    public function getStatusAttribute()
    {
        $now = Carbon::now();
        if ($this->tanggal_waktu->isFuture()) {
            return 'Upcoming';
        } elseif ($now->between($this->tanggal_waktu, $this->tanggal_waktu->copy()->addHours(3))) {
            return 'Ongoing';
        } else {
            return 'Completed';
        }
    }

    public function getImageUrlAttribute()
    {
        $url = $this->gambar;
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        if ($url && \Illuminate\Support\Facades\Storage::disk('public')->exists($url)) {
            return asset('storage/' . $url);
        }
        return asset('images/konser.jpg');
    }

    public function hasSales()
    {
        return $this->orders()->exists();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', Carbon::now());
    }

    public function scopeOngoing($query)
    {
        return $query->whereBetween('tanggal_waktu', [Carbon::now()->subHours(3), Carbon::now()]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', Carbon::now()->subHours(3));
    }

    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
