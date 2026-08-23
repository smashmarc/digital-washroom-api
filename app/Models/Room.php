<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'qr_code',
        'records_to_show',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($room) {
    //         // If no qr_code is manually set, generate one
    //         if (empty($room->qr_code)) {
    //             $room->qr_code = self::generateQrCode();
    //         }
    //     });
    // }

    /**
     * Generate a unique QR code string
     */
    public static function generateQrCode()
    {
        do {
            // Example: ROOM-<random 8 chars>
            $code = 'ROOM-' . Str::upper(Str::random(8));
        } while (self::where('qr_code', $code)->exists());

        return $code;
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class)->latest(); // latest first
    }

    // optional helper to get last cleaned log
    public function lastCleanedLog()
    {
        return $this->hasMany(Log::class)
        ->where('note_code', '!=', 0)
        ->orderByDesc('id')
        ->limit($this->records_to_show ?: 3);
    }

    
}
