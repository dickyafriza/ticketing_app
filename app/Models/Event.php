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
        if ($this->tanggal_waktu->isFuture()) {
            return 'Upcoming';
        } elseif ($this->tanggal_waktu->isToday()) {
            return 'Ongoing';
        } else {
            return 'Completed';
        }
    }

    public function getImageUrlAttribute()
    {
        return $this->gambar ? asset('storage/' . $this->gambar) : asset('images/default-event.jpg');
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
        return $query->whereDate('tanggal_waktu', Carbon::today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', Carbon::today());
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
