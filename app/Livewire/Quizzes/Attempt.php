<?php

namespace App\Livewire\Quizzes;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\CourseProgress;
use App\Services\QuizService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Attempt extends Component
{
    public Quiz $quiz;
    public QuizAttempt $attempt;

    // Repaso de las preguntas falladas (no guarda nada)
    public bool $practice = false;
    public array $practiceAnswers = [];
    public array $checked = [];

    public function mount(Quiz $quiz, QuizAttempt $attempt)
    {
        $this->quiz = $quiz;
        $this->attempt = $attempt;

        $user = auth()->user();
        $isOwner = (int) $attempt->user_id === (int) $user->id;

        if ((int) $attempt->quiz_id !== (int) $quiz->id || (! $isOwner && ! $user->isAdmin())) {
            abort(404);
        }

        // Si todavía está en curso, se vuelve al quiz para retomarlo.
        if ($attempt->isInProgress()) {
            $this->redirectRoute('quizzes.show', ['quiz' => $quiz->id], navigate: true);
        }
    }

    public function startPractice(): void
    {
        if (! $this->canShow()) {
            return;
        }

        $this->practiceAnswers = [];
        $this->checked = [];

        foreach ($this->wrongRows() as $row) {
            $this->practiceAnswers[$row->question->id] = $this->emptyAnswer($row->question->type);
        }

        $this->practice = true;
    }

    public function stopPractice(): void
    {
        $this->practice = false;
        $this->practiceAnswers = [];
        $this->checked = [];
    }

    public function check(int $questionId): void
    {
        if (! $this->canShow()) {
            return;
        }

        $row = $this->wrongRows()->first(fn ($r) => $r->question->id === $questionId);

        if (! $row) {
            return;
        }

        $this->checked[$questionId] = app(QuizService::class)
            ->checkAnswer($row->question, $this->practiceAnswers[$questionId] ?? null);
    }

    public function retry(int $questionId): void
    {
        $row = $this->wrongRows()->first(fn ($r) => $r->question->id === $questionId);

        if (! $row) {
            return;
        }

        unset($this->checked[$questionId]);
        $this->practiceAnswers[$questionId] = $this->emptyAnswer($row->question->type);
    }

    // ───────────────────────── internos ─────────────────────────

    protected function emptyAnswer(string $type): array|string
    {
        return $type === 'multiple_choice' ? [] : '';
    }

    protected function canShow(): bool
    {
        return auth()->user()->isAdmin()
            || app(QuizService::class)->canShowAnswers($this->attempt);
    }

    protected function loadedAttempt(): QuizAttempt
    {
        return $this->attempt->fresh(['attemptQuestions.question.options', 'answers']);
    }

    protected function buildRows(QuizAttempt $attempt): Collection
    {
        $answers = $attempt->answers->keyBy('question_id');

        return $attempt->attemptQuestions->map(function ($slot) use ($answers) {
            $question = $slot->question;
            $answer = $answers->get($question->id);

            $order = $slot->option_order ?: $question->options->pluck('id')->all();

            $options = collect($order)
                ->map(fn ($id) => $question->options->firstWhere('id', $id))
                ->filter()
                ->values();

            $selected = collect($answer?->selected_option_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $text = $answer?->answer_text;

            return (object) [
                'question' => $question,
                'options' => $options,
                'selected' => $selected,
                'text' => $text,
                'correct' => (bool) ($answer?->is_correct),
                'answered' => ! empty($selected) || trim((string) $text) !== '',
            ];
        })->values();
    }

    protected function wrongRows(): Collection
    {
        return $this->buildRows($this->loadedAttempt())
            ->filter(fn ($row) => ! $row->correct)
            ->values();
    }

    protected function duration(): ?string
    {
        if (! $this->attempt->started_at || ! $this->attempt->completed_at) {
            return null;
        }

        $seconds = max(0, $this->attempt->completed_at->timestamp - $this->attempt->started_at->timestamp);

        if ($seconds < 1) {
            return null;
        }

        return $seconds >= 60
            ? intdiv($seconds, 60) . ' min ' . ($seconds % 60) . ' s'
            : $seconds . ' s';
    }

    /**
     * Qué sigue después de este intento (solo para el dueño del intento).
     */
    protected function nextAction($user): ?array
    {
        $subject = $this->quiz->resolvedSubject();

        if (! $subject) {
            return null;
        }

        $service = app(QuizService::class);
        $progress = app(CourseProgress::class);

        $status = $service->status($user, $this->quiz);
        $state = $progress->subjectState($user, $subject);
        $label = $this->quiz->isFinal() ? 'examen' : 'quiz';

        if ($this->attempt->passed) {
            if ($state->completed) {
                return [
                    'tone' => 'success',
                    'message' => '¡Completaste esta materia! Excelente trabajo.',
                    'label' => 'Ir a Mi aprendizaje',
                    'url' => route('my-learning'),
                ];
            }

            if (! $this->quiz->isFinal()) {
                $topicState = $state->topics->first(fn ($s) => $s->topic->id === $this->quiz->topic_id);

                if ($topicState && ! $topicState->studied) {
                    return [
                        'tone' => 'info',
                        'message' => 'Aprobaste el quiz. Para completar el tema, márcalo como estudiado.',
                        'label' => 'Ir al tema',
                        'url' => route('topics.show', $topicState->topic),
                    ];
                }
            }

            return [
                'tone' => 'success',
                'message' => 'Sigue avanzando en la materia.',
                'label' => 'Continuar',
                'url' => $progress->stepUrl($state->next, $subject),
            ];
        }

        // No aprobó este intento, pero ya había aprobado antes: fue práctica.
        if ($status->passed) {
            return [
                'tone' => 'info',
                'message' => "Este intento fue de práctica: tu aprobación del {$label} no cambia.",
                'label' => 'Continuar',
                'url' => $progress->stepUrl($state->next, $subject),
            ];
        }

        if ($status->can_start) {
            $remaining = $status->attempts_remaining;

            return [
                'tone' => 'warning',
                'message' => $remaining !== null
                    ? 'Aún no alcanzas la nota mínima. Te quedan ' . $remaining . ($remaining === 1 ? ' intento.' : ' intentos.')
                    : 'Aún no alcanzas la nota mínima. Puedes volver a intentarlo cuando quieras.',
                'label' => 'Intentar de nuevo',
                'url' => route('quizzes.show', $this->quiz),
            ];
        }

        if ($status->locked_until) {
            return [
                'tone' => 'warning',
                'message' => 'Agotaste tus intentos. Podrás intentarlo de nuevo '
                    . $status->locked_until->copy()->locale('es')->diffForHumans() . '. Aprovecha para repasar el material.',
                'label' => $this->quiz->isFinal() ? 'Volver a la materia' : 'Repasar el tema',
                'url' => (! $this->quiz->isFinal() && $this->quiz->topic)
                    ? route('topics.show', $this->quiz->topic)
                    : route('subjects.show', $subject),
            ];
        }

        return null;
    }

    public function render()
    {
        $user = auth()->user();
        $owner = (int) $this->attempt->user_id === (int) $user->id;

        $rows = $this->buildRows($this->loadedAttempt());

        return view('livewire.quizzes.attempt', [
            'owner' => $owner,
            'showAnswers' => $this->canShow(),
            'rows' => $rows,
            'wrongRows' => $rows->filter(fn ($row) => ! $row->correct)->values(),
            'next' => $owner ? $this->nextAction($user) : null,
            'duration' => $this->duration(),
            'subject' => $this->quiz->resolvedSubject(),
        ]);
    }
}
