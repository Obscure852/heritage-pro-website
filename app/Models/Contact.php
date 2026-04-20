<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function people(): HasMany
    {
        return $this->hasMany(ContactPerson::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id');
    }

    public function primaryPerson(): HasOne
    {
        return $this->hasOne(ContactPerson::class)->where('is_primary', true)->orderBy('sort_order')->orderBy('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContactTag::class, 'contact_contact_tag')
            ->withTimestamps()
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'contact_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class, 'contact_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEligibleForAssets($query)
    {
        return $query->active()->whereHas('tags', function ($tagQuery) {
            $tagQuery->where('is_active', true)->where('usable_in_assets', true);
        });
    }

    public function scopeEligibleForMaintenance($query)
    {
        return $query->active()->whereHas('tags', function ($tagQuery) {
            $tagQuery->where('is_active', true)->where('usable_in_maintenance', true);
        });
    }

    public function getContactPersonAttribute(): ?string
    {
        if ($this->relationLoaded('primaryPerson')) {
            return $this->primaryPerson?->name;
        }

        return $this->primaryPerson()->value('name');
    }

    public function getPrimaryPersonLabelAttribute(): ?string
    {
        $person = $this->relationLoaded('primaryPerson')
            ? $this->primaryPerson
            : $this->primaryPerson()->first();

        if (!$person) {
            return null;
        }

        $parts = array_filter([$person->name, $person->title]);

        return implode(' - ', $parts);
    }
}
