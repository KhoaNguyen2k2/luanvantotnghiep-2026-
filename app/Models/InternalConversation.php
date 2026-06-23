<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'staff_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function messages()
    {
        return $this->hasMany(InternalMessage::class);
    }
}
