<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DiscussionMessage extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_messages';

    protected $fillable = [
        'thread_id',
        'user_id',
        'direction',
        'channel',
        'body',
        'delivery_status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DiscussionMessageAttachment::class, 'message_id')->orderBy('id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(DiscussionMessageMention::class, 'message_id')->orderBy('id');
    }

    public function renderedBody(?User $viewer = null): string
    {
        if ($this->channel === 'email') {
            return static::emailBodyAsHtml((string) $this->body);
        }

        $body = nl2br(e((string) $this->body));

        foreach ($this->resolvedMentions()->sortByDesc(fn (DiscussionMessageMention $mention) => strlen($mention->label())) as $mention) {
            $label = $mention->label();

            if ($label === '') {
                continue;
            }

            $classes = 'crm-discussion-mention';

            if ($viewer && (int) $mention->user_id === (int) $viewer->id) {
                $classes .= ' is-personal';
            }

            $plainToken = e('@' . $label);
            $legacyToken = e('@[' . $label . ']');
            $replacement = '<span class="' . $classes . '">' . $plainToken . '</span>';
            $placeholder = '__CRM_MENTION_' . $mention->id . '__';

            $body = str_replace($legacyToken, $placeholder, $body);
            $body = str_replace($plainToken, $placeholder, $body);
            $body = str_replace($placeholder, $replacement, $body);
        }

        return $body;
    }

    public static function emailBodyAsHtml(string $body): string
    {
        if (! str_contains($body, '<')) {
            return nl2br(e($body));
        }

        $sanitized = trim((string) preg_replace('/\s+/', ' ', static::sanitizeEmailHtml($body)));

        if ($sanitized === '') {
            return nl2br(e(strip_tags($body)));
        }

        return static::sanitizeEmailHtml($body);
    }

    public static function emailBodyPreview(string $body, int $limit = 220): string
    {
        $plainText = trim((string) preg_replace('/\s+/', ' ', strip_tags($body)));

        return Str::limit($plainText, $limit);
    }

    public function mentionsUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->resolvedMentions()->contains(function (DiscussionMessageMention $mention) use ($user): bool {
            return (int) $mention->user_id === (int) $user->id;
        });
    }

    public function activityAt()
    {
        return $this->sent_at ?: $this->created_at;
    }

    private function resolvedMentions(): Collection
    {
        if (! $this->relationLoaded('mentions')) {
            $this->loadMissing('mentions.user');
        } else {
            $this->mentions->loadMissing('user');
        }

        return $this->mentions;
    }

    private static function sanitizeEmailHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><s><ol><ul><li><blockquote><a><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><td><th>';
        $sanitized = strip_tags($html, $allowedTags);

        $sanitized = preg_replace('/\sstyle=("|\').*?\1/i', '', $sanitized);
        $sanitized = preg_replace('/\son\w+=("|\').*?\1/i', '', $sanitized);
        $sanitized = preg_replace('/\son\w+=\S+/i', '', $sanitized);
        $sanitized = preg_replace('/javascript:/i', '', $sanitized);

        return (string) $sanitized;
    }
}
