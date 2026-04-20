<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LibraryCollection extends Model {
    protected $table = 'lms_library_collections';

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'parent_id',
        'visibility',
        'created_by',
    ];

    public static array $visibilities = [
        'private' => 'Private - Only you',
        'shared' => 'Shared - Specific users/roles',
        'public' => 'Public - All instructors',
    ];

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function items(): HasMany {
        return $this->hasMany(LibraryItem::class, 'collection_id');
    }

    public function shares(): MorphMany {
        return $this->morphMany(LibraryCollectionShare::class, 'shareable');
    }

    public function scopeAccessibleBy($query, User $user) {
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('visibility', 'public')
              ->orWhereHas('shares', function ($sq) use ($user) {
                  $sq->where('shareable_type', User::class)
                    ->where('shareable_id', $user->id);
              });
        });
    }

    public function scopeRootLevel($query) {
        return $query->whereNull('parent_id');
    }

    public function getItemCountAttribute(): int {
        return $this->items()->count();
    }

    public function getBreadcrumbAttribute(): array {
        $breadcrumb = [$this];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($breadcrumb, $parent);
            $parent = $parent->parent;
        }

        return $breadcrumb;
    }
}
