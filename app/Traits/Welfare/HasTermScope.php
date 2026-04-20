<?php

namespace App\Traits\Welfare;

use App\Helpers\TermHelper;
use Illuminate\Database\Eloquent\Builder;

/**
 * Provides term-based scoping for welfare models.
 *
 * All welfare transactional models should use this trait to ensure
 * data is properly filtered by academic term.
 */
trait HasTermScope
{
    /**
     * Scope query to a specific term.
     *
     * @param Builder $query
     * @param int|null $termId Term ID to filter by. Defaults to session or current term.
     * @return Builder
     */
    public function scopeInTerm(Builder $query, ?int $termId = null): Builder
    {
        $termId = $termId ?? $this->resolveTermId();

        return $query->where($this->getTable() . '.term_id', $termId);
    }

    /**
     * Scope query to a specific year.
     *
     * @param Builder $query
     * @param int|null $year Year to filter by. Defaults to current year.
     * @return Builder
     */
    public function scopeInYear(Builder $query, ?int $year = null): Builder
    {
        $year = $year ?? (int) date('Y');

        return $query->where($this->getTable() . '.year', $year);
    }

    /**
     * Scope query to current term (based on TermHelper).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentTerm(Builder $query): Builder
    {
        $currentTerm = TermHelper::getCurrentTerm();

        if (!$currentTerm) {
            // Return empty result set if no current term
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable() . '.term_id', $currentTerm->id);
    }

    /**
     * Scope query to term and year combination.
     *
     * @param Builder $query
     * @param int|null $termId
     * @param int|null $year
     * @return Builder
     */
    public function scopeInTermAndYear(Builder $query, ?int $termId = null, ?int $year = null): Builder
    {
        return $query->inTerm($termId)->inYear($year);
    }

    /**
     * Resolve the term ID from session or current term.
     *
     * @return int|null
     */
    protected function resolveTermId(): ?int
    {
        // First check session for user-selected term
        $sessionTermId = session('selected_term_id');

        if ($sessionTermId) {
            return (int) $sessionTermId;
        }

        // Fall back to current term from helper
        $currentTerm = TermHelper::getCurrentTerm();

        return $currentTerm?->id;
    }

    /**
     * Boot method to auto-fill term_id and year on creating.
     * Call this from your model's boot method if needed.
     */
    protected static function bootHasTermScope(): void
    {
        static::creating(function ($model) {
            // Auto-fill term_id if not set
            if (empty($model->term_id)) {
                $currentTerm = TermHelper::getCurrentTerm();
                if ($currentTerm) {
                    $model->term_id = $currentTerm->id;
                }
            }

            // Auto-fill year if not set
            if (empty($model->year)) {
                $currentTerm = TermHelper::getCurrentTerm();
                $model->year = $currentTerm?->year ?? (int) date('Y');
            }
        });
    }

    /**
     * Get the term relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(\App\Models\Term::class);
    }
}
