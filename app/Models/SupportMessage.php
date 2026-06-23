<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    public const SENDER_CUSTOMER = 'customer';
    public const SENDER_STAFF = 'staff';
    public const SENDER_SYSTEM = 'system';

    protected $fillable = [
        'support_conversation_id',
        'sender_id',
        'sender_type',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
