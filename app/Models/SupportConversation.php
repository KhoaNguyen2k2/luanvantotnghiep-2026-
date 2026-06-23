<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportConversation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'customer_id',
        'staff_id',
        'status',
        'rejected_staff_ids',
        'accepted_at',
        'closed_at',
    ];

    protected $casts = [
        'rejected_staff_ids' => 'array',
        'accepted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }
}
