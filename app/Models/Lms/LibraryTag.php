<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class LibraryTag extends Model {
    protected $table = 'lms_library_tags';

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    protected static function boot() {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function items(): BelongsToMany {
        return $this->belongsToMany(LibraryItem::class, 'lms_library_item_tag', 'tag_id', 'item_id');
    }

    public function getItemCountAttribute(): int {
        return $this->items()->count();
    }

    public static function findOrCreateByName(string $name): self {
        $slug = Str::slug($name);
        return self::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }
}
