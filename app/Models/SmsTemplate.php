<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'content',
        'description',
        'placeholders',
        'created_by',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Available placeholder variables that can be used in templates
     */
    public const AVAILABLE_PLACEHOLDERS = [
        '{student_name}' => 'Student full name',
        '{student_first_name}' => 'Student first name',
        '{student_last_name}' => 'Student last name',
        '{sponsor_name}' => 'Sponsor/Parent name',
        '{school_name}' => 'School name',
        '{class_name}' => 'Class/Grade name',
        '{term_name}' => 'Current term name',
        '{date}' => 'Current date',
        '{time}' => 'Current time',
        '{balance}' => 'Account balance',
        '{amount}' => 'Amount (for fees)',
    ];

    /**
     * Template categories
     */
    public const CATEGORIES = [
        'general' => 'General',
        'fees' => 'Fees & Payments',
        'attendance' => 'Attendance',
        'academic' => 'Academic',
        'events' => 'Events',
        'emergency' => 'Emergency',
    ];

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get character count of the template content
     */
    public function getCharacterCountAttribute()
    {
        return strlen($this->content);
    }

    /**
     * Get SMS unit count for this template
     */
    public function getSmsUnitsAttribute()
    {
        return ceil(strlen($this->content) / 160);
    }

    /**
     * Replace placeholders in the template with actual values
     *
     * @param array $values Key-value pairs of placeholder => value
     * @return string The processed message
     */
    public function processTemplate(array $values): string
    {
        $message = $this->content;

        foreach ($values as $placeholder => $value) {
            // Ensure placeholder is wrapped in braces
            if (!str_starts_with($placeholder, '{')) {
                $placeholder = '{' . $placeholder . '}';
            }
            $message = str_replace($placeholder, $value, $message);
        }

        return $message;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Extract placeholders from the content
     */
    public function extractPlaceholders(): array
    {
        preg_match_all('/\{([^}]+)\}/', $this->content, $matches);
        return $matches[0] ?? [];
    }
}
