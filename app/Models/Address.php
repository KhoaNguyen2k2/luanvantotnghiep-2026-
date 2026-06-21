<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'locality',
        'address',
        'city',
        'state',
        'country',
        'landmark',
        'zip',
        'type',
        'isdefault',
    ];

    protected function casts(): array
    {
        return [
            'isdefault' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
