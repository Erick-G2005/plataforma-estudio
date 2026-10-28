<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    public const TYPE_TOPIC = 'topic';
    public const TYPE_FINAL = 'final';

    public const SHOW_NEVER = 'never';
    public const SHOW_AFTER_PASS = 'after_pass';
    public const SHOW_AFTER_ATTEMPT = 'after_attempt';

    protected $fillable = [
        'type', 'topic_id', 'subject_id', 'title',
        'pass_score', 'max_attempts', 'cooldown_hours', 'time_limit_minutes',
        'questions_per_attempt', 'drawn_questions_count', 'show_answers',
    ];

    protected $casts = [
        'pass_score' => 'integer',
        'max_attempts' => 'integer',
        'cooldown_hours' => 'integer',
        'time_limit_minutes' => 'integer',
        'questions_per_attempt' => 'integer',
        'drawn_questions_count' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isFinal(): bool
    {
        return $this->type === self::TYPE_FINAL;
    }

    /**
     * La materia a la que pertenece: directa (examen final) o a través del tema.
     */
    public function resolvedSubject(): ?Subject
    {
        return $this->isFinal() ? $this->subject : $this->topic?->subject;
    }

    /**
     * Pantalla de administración donde se edita este quiz o examen.
     */
    public function adminUrl(): string
    {
        return $this->isFinal()
            ? route('admin.subjects.exam', $this->subject_id)
            : route('admin.topics.quiz', $this->topic_id);
    }
}
