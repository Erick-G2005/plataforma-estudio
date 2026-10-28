<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Progreso de estudio del usuario (uno por cada tema que ha tocado).
     */
    public function studyProgress(): HasMany
    {
        return $this->hasMany(StudyProgress::class);
    }

    /**
     * Indica si el usuario ya completó un tema en específico.
     */
    public function hasCompleted(Topic $topic): bool
    {
        return $this->studyProgress()
            ->where('topic_id', $topic->id)
            ->where('completed', true)
            ->exists();
    }
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function enrolledSubjects(): BelongsToMany
{
    return $this->belongsToMany(Subject::class, 'enrollments')->withTimestamps();
}

public function isEnrolledIn(Subject $subject): bool
{
    return $this->enrolledSubjects()->where('subjects.id', $subject->id)->exists();
}
public function enrolledSubjectsWithProgress()
{
    $completedIds = $this->studyProgress()
        ->where('completed', true)
        ->pluck('topic_id')
        ->all();

    return $this->enrolledSubjects()
        ->with('topics')
        ->orderBy('subjects.order')
        ->get()
        ->map(function ($subject) use ($completedIds) {
            $total = $subject->topics->count();
            $done = $subject->topics->whereIn('id', $completedIds)->count();

            $subject->total_topics = $total;
            $subject->completed_topics = $done;
            $subject->progress = $total > 0 ? (int) round($done / $total * 100) : 0;
            $subject->next_topic = $subject->topics
                ->first(fn ($topic) => ! in_array($topic->id, $completedIds));

            return $subject;
        });
}
}