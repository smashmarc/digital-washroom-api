<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BackupDestination extends Model
{
    protected $fillable = [
        'label', 'type', 'config',
        'is_active', 'last_used_at',
        'last_status', 'last_error',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Encrypt config on save, decrypt on read
    public function setConfigAttribute(array $value): void
    {
        $this->attributes['config'] = Crypt::encryptString(json_encode($value));
    }

    public function getConfigAttribute(string $value): array
    {
        return json_decode(Crypt::decryptString($value), true);
    }
}