<?php

namespace App\Models;

use App\Models\Library\LibraryTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model{
    use HasFactory;

    protected $fillable = [
        'isbn', 'title', 'author_id', 'grade_id', 'publication_year', 'publisher_id',
        'edition', 'genre', 'filter', 'call_number', 'language', 'format',
        'pages', 'description', 'cover_image_url', 'quantity',
        'status', 'location', 'date_added', 'price', 'currency', 'barcode',
        'dewey_decimal', 'series_name', 'volume_number', 'keywords',
        'reading_level', 'condition'
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'pages' => 'integer',
        'quantity' => 'integer',
        'date_added' => 'date:d/m/Y',
        'price' => 'decimal:2',
        'volume_number' => 'integer',
    ];

    public function author(){
        return $this->belongsTo(Author::class);
    }

    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function publisher(){
        return $this->belongsTo(Publisher::class);
    }

    public function copies(){
        return $this->hasMany(Copy::class);
    }

    public function authors(){
        return $this->belongsToMany(Author::class, 'book_author');
    }

    public function libraryTransactions(){
        return $this->hasManyThrough(LibraryTransaction::class, Copy::class);
    }

    public function getAuthorsListAttribute(){
        if ($this->relationLoaded('authors') && $this->authors->isNotEmpty()) {
            return $this->authors->map(fn($a) => $a->full_name)->implode(', ');
        }

        if ($this->relationLoaded('author') && $this->author) {
            return $this->author->full_name;
        }

        return $this->author?->full_name ?? 'Unknown';
    }
}
