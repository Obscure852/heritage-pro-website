<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ContactTag extends Model
{
    use HasFactory;

    public const DEFAULT_VENDOR_SLUG = 'vendor';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'usable_in_assets',
        'usable_in_maintenance',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usable_in_assets' => 'boolean',
        'usable_in_maintenance' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_contact_tag')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function buildSlug(string $name): string
    {
        return Str::slug($name);
    }
}
