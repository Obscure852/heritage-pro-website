<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'body',
        'variables',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include templates of a given type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Render template with provided data
     *
     * Replaces {{variable}} placeholders with actual data
     *
     * @param array $data Key-value pairs for variable replacement
     * @return array Rendered subject and body
     */
    public function render(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;

        // Replace {{variable}} with actual values
        if (!empty($this->variables)) {
            foreach ($this->variables as $variable) {
                $placeholder = '{{' . $variable . '}}';
                $value = $data[$variable] ?? '';

                if ($subject) {
                    $subject = str_replace($placeholder, $value, $subject);
                }
                $body = str_replace($placeholder, $value, $body);
            }
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Extract variables from template body
     *
     * Finds all {{variable}} patterns in body and subject
     */
    public static function extractVariables(string $body, ?string $subject = null): array
    {
        $variables = [];
        $text = $body . ' ' . ($subject ?? '');

        // Match {{variable}} pattern
        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);

        if (!empty($matches[1])) {
            $variables = array_unique($matches[1]);
        }

        return array_values($variables);
    }
}
