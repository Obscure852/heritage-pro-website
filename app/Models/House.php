<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class House extends Model{
    use HasFactory;
    protected $table ='houses';

    protected $fillable = [
        'name',
        'color_code',
        'head',
        'assistant',
        'term_id',
        'year',
    ];


    public function houseHead(): BelongsTo{
        return $this->belongsTo(User::class, 'head');
    }
    
    public function houseAssistant(): BelongsTo{
        return $this->belongsTo(User::class, 'assistant');
    }

    public function term(): BelongsTo{
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function students(): BelongsToMany{
        return $this->belongsToMany(Student::class, 'student_house', 'house_id', 'student_id')->withPivot('term_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_house', 'house_id', 'user_id')->withPivot('term_id')->withTimestamps();
    }

    public function getContrastTextColorAttribute(): string
    {
        $color = ltrim((string) $this->color_code, '#');

        if (strlen($color) !== 6 || !ctype_xdigit($color)) {
            return '#111827';
        }

        $red = hexdec(substr($color, 0, 2));
        $green = hexdec(substr($color, 2, 2));
        $blue = hexdec(substr($color, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance >= 160 ? '#111827' : '#FFFFFF';
    }

    public function colorWithAlpha(float $alpha = 0.16): string
    {
        $alpha = max(0, min(1, $alpha));
        $color = ltrim((string) $this->color_code, '#');

        if (strlen($color) !== 6 || !ctype_xdigit($color)) {
            return 'rgba(37, 99, 235, '.$alpha.')';
        }

        $red = hexdec(substr($color, 0, 2));
        $green = hexdec(substr($color, 2, 2));
        $blue = hexdec(substr($color, 4, 2));

        return sprintf('rgba(%d, %d, %d, %.2f)', $red, $green, $blue, $alpha);
    }
}
