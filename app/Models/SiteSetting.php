<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getInt(string $key, int $default, int $min = 1, int $max = 5): int
    {
        $value = (int) (static::where('key', $key)->value('value') ?? $default);

        return max($min, min($max, $value));
    }

    public static function putValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
