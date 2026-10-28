<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Ciclo de vida de un intento: iniciar, guardar respuestas, enviar y calificar,
 * más las reglas de intentos, tiempo y espera.
 */
class QuizService
{
    public function __construct(private CourseProgress $progress)
    {
    }

    /**
     * Estado del estudiante frente a un quiz: si puede iniciar, intentos, espera, etc.
     */
    public function status(User $user, Quiz $quiz): object
    {
        $this->closeExpiredAttempts($user, $quiz);

        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->orderBy('started_at')
            ->orderBy('id')
            ->get();

        $passed = $attempts->contains('passed', true);
        $inProgress = $attempts->firstWhere('status', QuizAttempt::STATUS_IN_PROGRESS);
        $finished = $attempts->where('status', '!=', QuizAttempt::STATUS_IN_PROGRESS);
        $best = $finished->isNotEmpty() ? (float) $finished->max('percentage') : null;

        $limited = ! $passed && $quiz->max_attempts;
        $used = 0;
        $lockedUntil = null;

        if ($limited) {
            [$used, $lockedUntil] = $this->currentCycle($attempts, $quiz);
        }

        $canStart = ! $inProgress
            && ! $lockedUntil
            && (! $limited || $used < $quiz->max_attempts)
            && $this->hasQuestions($quiz);

        return (object) [
            'passed' => $passed,
            'practice' => $passed, // ya aprobado: puede repetirlo sin límites ni espera
            'in_progress' => $inProgress,
            'best_percentage' => $best,
            'attempts_total' => $finished->count(),
            'attempts_used' => $used,
            'attempts_remaining' => $limited ? max(0, $quiz->max_attempts - $used) : null,
            'locked_until' => $lockedUntil,
            'can_start' => $canStart,
        ];
    }

    /**
     * Cantidad aproximada de preguntas que tendrá un intento de este quiz.
     */
    public function expectedQuestionCount(Quiz $quiz): int
    {
        $count = $quiz->questions()->count();

        if ($quiz->questions_per_attempt) {
            $count = min($count, $quiz->questions_per_attempt);
        }

        if ($quiz->isFinal() && $quiz->drawn_questions_count > 0) {
            $count += min($quiz->drawn_questions_count, $this->topicQuestionsQuery($quiz)->count());
        }

        return $count;
    }

    /**
     * Inicia un intento (o devuelve el que ya está en curso).
     * Iniciar un intento lo consume, aunque el estudiante lo abandone.
     */
    public function start(User $user, Quiz $quiz): QuizAttempt
    {
        $status = $this->status($user, $quiz);

        if ($status->in_progress) {
            return $status->in_progress;
        }

        if (! $status->can_start) {
            throw new RuntimeException('No puedes iniciar este quiz en este momento.');
        }

        $questions = $this->pickQuestions($quiz);

        if ($questions->isEmpty()) {
            throw new RuntimeException('Este quiz todavía no tiene preguntas.');
        }

        return DB::transaction(function () use ($user, $quiz, $questions) {
            $now = now();

            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'status' => QuizAttempt::STATUS_IN_PROGRESS,
                'score' => 0,
                'total_questions' => $questions->count(),
                'started_at' => $now,
                'expires_at' => $quiz->time_limit_minutes
                    ? $now->copy()->addMinutes($quiz->time_limit_minutes)
                    : null,
                'percentage' => 0,
                'passed' => false,
            ]);

            foreach ($questions->values() as $position => $question) {
                $attempt->attemptQuestions()->create([
                    'question_id' => $question->id,
                    'position' => $position + 1,
                    'option_order' => $question->options->shuffle()->pluck('id')->values()->all(),
                ]);
            }

            return $attempt;
        });
    }

    /**
     * Guarda (o actualiza) la respuesta a una pregunta mientras el intento está en curso.
     * $value: id de opción, lista de ids (selección múltiple) o texto (respuesta corta).
     */
    public function saveAnswer(QuizAttempt $attempt, int $questionId, mixed $value): void
    {
        if (! $attempt->isInProgress() || $attempt->isPastDeadline()) {
            return;
        }

        $slot = $attempt->attemptQuestions()
            ->where('question_id', $questionId)
            ->with('question')
            ->first();

        if (! $slot) {
            return;
        }

        $payload = $slot->question->type === 'short_answer'
            ? [
                'selected_option_ids' => null,
                'answer_text' => is_string($value) ? trim($value) : null,
            ]
            : [
                'selected_option_ids' => array_values(array_map('intval', (array) $value)),
                'answer_text' => null,
            ];

        QuizAttemptAnswer::updateOrCreate(
            ['quiz_attempt_id' => $attempt->id, 'question_id' => $questionId],
            $payload
        );
    }

    /**
     * Envía el intento; si ya venció el tiempo, se califica como vencido con lo respondido.
     */
    public function finish(QuizAttempt $attempt): QuizAttempt
    {
        return $this->submit($attempt, expired: $attempt->isPastDeadline());
    }

    /**
     * Califica el intento y, si aprueba un quiz de tema, intenta completar el tema.
     */
    public function submit(QuizAttempt $attempt, bool $expired = false): QuizAttempt
    {
        if (! $attempt->isInProgress()) {
            return $attempt;
        }

        $quiz = $attempt->quiz;
        $attempt->load(['attemptQuestions.question.options']);
        $answers = $attempt->answers()->get()->keyBy('question_id');

        $correct = 0;

        foreach ($attempt->attemptQuestions as $slot) {
            $question = $slot->question;
            $answer = $answers->get($question->id);
            $isCorrect = $this->isCorrect($question, $answer);

            if ($answer) {
                $answer->update(['is_correct' => $isCorrect]);
            } else {
                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option_ids' => null,
                    'answer_text' => null,
                    'is_correct' => false,
                ]);
            }

            if ($isCorrect) {
                $correct++;
            }
        }

        $total = $attempt->attemptQuestions->count();
        $percentage = $total > 0 ? round($correct * 100 / $total, 2) : 0;
        $passed = $percentage >= $quiz->pass_score;
        $finishedAt = ($expired && $attempt->expires_at) ? $attempt->expires_at : now();

        $attempt->update([
            'status' => $expired ? QuizAttempt::STATUS_EXPIRED : QuizAttempt::STATUS_SUBMITTED,
            'score' => $correct,
            'total_questions' => $total,
            'percentage' => $percentage,
            'passed' => $passed,
            'completed_at' => $finishedAt,
        ]);

        if ($passed && ! $quiz->isFinal() && $quiz->topic) {
            $this->progress->syncTopicCompletion($attempt->user, $quiz->topic);
        }

        return $attempt->fresh();
    }

    /**
     * ¿Se pueden mostrar las respuestas correctas de este intento? Depende de la regla del quiz.
     */
    public function canShowAnswers(QuizAttempt $attempt): bool
    {
        $quiz = $attempt->quiz;

        return match ($quiz->show_answers) {
            Quiz::SHOW_NEVER => false,
            Quiz::SHOW_AFTER_PASS => $this->progress->hasPassed($attempt->user, $quiz),
            default => true,
        };
    }

    /**
     * Califica una respuesta suelta sin guardar nada (para el repaso de preguntas falladas).
     */
    public function checkAnswer(Question $question, mixed $value): bool
    {
        $question->loadMissing('options');

        $isText = $question->type === 'short_answer';

        $answer = new QuizAttemptAnswer([
            'selected_option_ids' => $isText ? null : array_values(array_map('intval', (array) $value)),
            'answer_text' => ($isText && is_string($value)) ? trim($value) : null,
        ]);

        return $this->isCorrect($question, $answer);
    }

    // ───────────────────────── internos ─────────────────────────

    /**
     * Califica como vencidos los intentos en curso cuyo tiempo ya se agotó.
     */
    protected function closeExpiredAttempts(User $user, Quiz $quiz): void
    {
        QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', QuizAttempt::STATUS_IN_PROGRESS)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get()
            ->each(fn (QuizAttempt $attempt) => $this->submit($attempt, expired: true));
    }

    /**
     * Intentos usados en el ciclo actual y hasta cuándo está bloqueado (si lo está).
     * Al agotar los intentos sin aprobar, se espera "cooldown_hours" desde el último
     * intento y luego se reinician todos.
     *
     * @return array{0: int, 1: \Illuminate\Support\Carbon|null}
     */
    protected function currentCycle(Collection $attempts, Quiz $quiz): array
    {
        $count = 0;
        $lockUntil = null;

        foreach ($attempts as $attempt) {
            // Si la espera ya había terminado cuando empezó este intento, arranca un ciclo nuevo.
            if ($lockUntil && $attempt->started_at && $attempt->started_at->gte($lockUntil)) {
                $count = 0;
                $lockUntil = null;
            }

            $count++;

            if ($count >= $quiz->max_attempts && $attempt->completed_at) {
                $lockUntil = $attempt->completed_at->copy()->addHours($quiz->cooldown_hours);
            }
        }

        if ($lockUntil && now()->lt($lockUntil)) {
            return [$count, $lockUntil];
        }

        // La espera terminó: se reinician todos los intentos.
        return [$lockUntil ? 0 : $count, null];
    }

    protected function hasQuestions(Quiz $quiz): bool
    {
        if ($quiz->questions()->exists()) {
            return true;
        }

        return $quiz->isFinal()
            && $quiz->drawn_questions_count > 0
            && $this->topicQuestionsQuery($quiz)->exists();
    }

    /**
     * Preguntas del intento: las propias del quiz (todas o un sorteo) y, en el examen
     * final, además las sorteadas de los quizzes de los temas.
     */
    protected function pickQuestions(Quiz $quiz): EloquentCollection
    {
        $own = $quiz->questions()->with('options')->get();

        if ($quiz->questions_per_attempt) {
            $own = $own->shuffle()->take($quiz->questions_per_attempt);
        }

        $picked = $own->all();

        if ($quiz->isFinal() && $quiz->drawn_questions_count > 0) {
            $picked = array_merge($picked, $this->drawFromTopics($quiz)->all());
        }

        return (new EloquentCollection($picked))->shuffle()->values();
    }

    /**
     * Sortea preguntas de los quizzes de los temas, repartidas parejo entre los temas.
     */
    protected function drawFromTopics(Quiz $quiz): EloquentCollection
    {
        $pools = $this->topicQuestionsQuery($quiz)
            ->with('options')
            ->get()
            ->groupBy('quiz_id')
            ->map(fn (Collection $group) => $group->shuffle()->values()->all())
            ->shuffle()
            ->values()
            ->all();

        $target = $quiz->drawn_questions_count;
        $picked = [];

        while (count($picked) < $target) {
            $tookAny = false;

            foreach ($pools as $i => $pool) {
                if (count($picked) >= $target) {
                    break;
                }

                if (! empty($pool)) {
                    $picked[] = array_shift($pools[$i]);
                    $tookAny = true;
                }
            }

            if (! $tookAny) {
                break; // ya no quedan preguntas en ningún tema
            }
        }

        return new EloquentCollection($picked);
    }

    protected function topicQuestionsQuery(Quiz $quiz)
    {
        $subject = $quiz->resolvedSubject();
        $topicIds = $subject ? $subject->topics()->pluck('id') : collect();
        $quizIds = Quiz::whereIn('topic_id', $topicIds)->pluck('id');

        return Question::query()->whereIn('quiz_id', $quizIds);
    }

    protected function isCorrect(Question $question, ?QuizAttemptAnswer $answer): bool
    {
        if (! $answer) {
            return false;
        }

        if ($question->type === 'short_answer') {
            $expected = mb_strtolower(trim((string) $question->correct_answer_text));
            $given = mb_strtolower(trim((string) $answer->answer_text));

            return $expected !== '' && $expected === $given;
        }

        $correctIds = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $selectedIds = collect($answer->selected_option_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return ! empty($correctIds) && $correctIds === $selectedIds;
    }
}
