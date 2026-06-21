<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    public const SCOPE_ORDER = 'order';

    public const SCOPE_CATEGORY = 'category';

    protected $fillable = [
        'code',
        'type',
        'value',
        'cart_value',
        'scope',
        'category_id',
        'expiry_date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'value' => 'decimal:2',
            'cart_value' => 'decimal:2',
        ];
    }
}
