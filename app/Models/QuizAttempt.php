<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id', 'quiz_id', 'status', 'score', 'total_questions',
        'started_at', 'expires_at', 'completed_at', 'percentage', 'passed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'percentage' => 'float',
        'passed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(QuizAttemptQuestion::class)->orderBy('position');
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isPastDeadline(): bool
    {
        return $this->isInProgress()
            && $this->expires_at !== null
            && now()->greaterThanOrEqualTo($this->expires_at);
    }

    /**
     * Segundos que le quedan al intento (null si no tiene límite de tiempo).
     */
    public function secondsLeft(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        return max(0, $this->expires_at->timestamp - now()->timestamp);
    }
}
