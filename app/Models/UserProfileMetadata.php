<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfileMetadata extends Model
{
    use HasFactory;

    protected $table = 'user_profile_metadata';

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function setValue(int $userId, string $key, $value): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }

    public static function getValue(int $userId, string $key, $default = null)
    {
        $record = static::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();

        return $record?->value ?? $default;
    }
}
