<?php

namespace App\Livewire\Quizzes;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\CourseProgress;
use App\Services\QuizService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class Take extends Component
{
    public Quiz $quiz;

    // Intento en curso. Bloqueado: el navegador no puede cambiarlo.
    #[Locked]
    public ?int $attemptId = null;

    public int $current = 0;
    public array $answers = [];
    public bool $reviewing = false;

    public function mount(Quiz $quiz)
    {
        $this->quiz = $quiz;
        $user = auth()->user();
        $progress = app(CourseProgress::class);

        if (! $progress->canAccessQuiz($user, $quiz)) {
            $subject = $quiz->resolvedSubject();

            if ($subject) {
                session()->flash('subject_notice', $progress->lockedMessage($user, $subject, $quiz->isFinal()));

                $this->redirectRoute('subjects.show', ['subject' => $subject->id], navigate: true);
            } else {
                $this->redirectRoute('dashboard', navigate: true);
            }

            return;
        }

        $status = app(QuizService::class)->status($user, $quiz);

        // Si dejó un intento a medias, lo retoma donde iba.
        if ($status->in_progress) {
            $this->loadAttempt($status->in_progress);
        }
    }

    public function start(): void
    {
        try {
            $attempt = app(QuizService::class)->start(auth()->user(), $this->quiz);
        } catch (RuntimeException $e) {
            session()->flash('quiz_error', $e->getMessage());

            return;
        }

        $this->loadAttempt($attempt);
    }

    public function next(): void
    {
        $attempt = $this->guard();

        if (! $attempt) {
            return;
        }

        $this->persistCurrent($attempt);

        $total = $attempt->attemptQuestions()->count();

        if ($this->current < $total - 1) {
            $this->current++;
        } else {
            $this->reviewing = true;
        }
    }

    public function previous(): void
    {
        $attempt = $this->guard();

        if (! $attempt) {
            return;
        }

        $this->persistCurrent($attempt);

        if ($this->current > 0) {
            $this->current--;
        }
    }

    public function goTo(int $index): void
    {
        $attempt = $this->guard();

        if (! $attempt) {
            return;
        }

        $this->persistCurrent($attempt);

        $total = $attempt->attemptQuestions()->count();

        $this->current = max(0, min($index, $total - 1));
        $this->reviewing = false;
    }

    public function review(): void
    {
        $attempt = $this->guard();

        if (! $attempt) {
            return;
        }

        $this->persistCurrent($attempt);
        $this->reviewing = true;
    }

    public function backToQuestions(): void
    {
        $this->reviewing = false;
    }

    /**
     * Envía el intento. También lo llama el cronómetro cuando llega a cero.
     */
    public function submit(): void
    {
        $attempt = $this->guard();

        if (! $attempt) {
            return;
        }

        $this->persistAll($attempt);

        $finished = app(QuizService::class)->finish($attempt);
        $this->attemptId = null;

        $this->redirectToResult($finished);
    }

    // ───────────────────────── internos ─────────────────────────

    protected function loadAttempt(QuizAttempt $attempt): void
    {
        $this->attemptId = $attempt->id;
        $this->current = 0;
        $this->reviewing = false;
        $this->answers = [];

        $saved = $attempt->answers()->get()->keyBy('question_id');

        foreach ($attempt->attemptQuestions()->with('question')->get() as $slot) {
            $question = $slot->question;
            $answer = $saved->get($question->id);
            $ids = $answer?->selected_option_ids ?? [];

            $this->answers[$question->id] = match ($question->type) {
                'multiple_choice' => array_map('strval', $ids),
                'short_answer' => (string) ($answer?->answer_text ?? ''),
                default => isset($ids[0]) ? (string) $ids[0] : null,
            };
        }
    }

    /**
     * El intento en curso de este usuario y este quiz (nunca uno ajeno).
     */
    protected function attempt(): ?QuizAttempt
    {
        if (! $this->attemptId) {
            return null;
        }

        $attempt = QuizAttempt::where('id', $this->attemptId)
            ->where('user_id', auth()->id())
            ->where('quiz_id', $this->quiz->id)
            ->where('status', QuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if (! $attempt) {
            $this->attemptId = null;
        }

        return $attempt;
    }

    /**
     * Devuelve el intento en curso; si el tiempo ya venció, lo califica y manda a resultados.
     */
    protected function guard(): ?QuizAttempt
    {
        $attempt = $this->attempt();

        if (! $attempt) {
            return null;
        }

        if ($attempt->isPastDeadline()) {
            $finished = app(QuizService::class)->finish($attempt);
            $this->attemptId = null;
            $this->redirectToResult($finished);

            return null;
        }

        return $attempt;
    }

    protected function redirectToResult(QuizAttempt $attempt): void
    {
        $this->redirectRoute('quizzes.attempt', [
            'quiz' => $this->quiz->id,
            'attempt' => $attempt->id,
        ], navigate: true);
    }

    protected function persistCurrent(QuizAttempt $attempt): void
    {
        if ($this->reviewing) {
            return;
        }

        $questionId = $attempt->attemptQuestions()->pluck('question_id')->values()->get($this->current);

        if ($questionId === null) {
            return;
        }

        app(QuizService::class)->saveAnswer($attempt, (int) $questionId, $this->answers[$questionId] ?? null);
    }

    protected function persistAll(QuizAttempt $attempt): void
    {
        $service = app(QuizService::class);

        foreach ($this->answers as $questionId => $value) {
            $service->saveAnswer($attempt, (int) $questionId, $value);
        }
    }

    protected function isAnswered(int $questionId): bool
    {
        $value = $this->answers[$questionId] ?? null;

        if (is_array($value)) {
            return count($value) > 0;
        }

        return $value !== null && trim((string) $value) !== '';
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(QuizService::class);
        $subject = $this->quiz->resolvedSubject();
        $attempt = $this->attempt();

        // Sin intento en curso: pantalla de presentación con reglas e historial.
        if (! $attempt) {
            return view('livewire.quizzes.take', [
                'mode' => 'intro',
                'subject' => $subject,
                'status' => $service->status($user, $this->quiz),
                'expected' => $service->expectedQuestionCount($this->quiz),
                'history' => QuizAttempt::where('user_id', $user->id)
                    ->where('quiz_id', $this->quiz->id)
                    ->where('status', '!=', QuizAttempt::STATUS_IN_PROGRESS)
                    ->orderByDesc('completed_at')
                    ->get(),
            ]);
        }

        $slots = $attempt->attemptQuestions()->with('question.options')->get();
        $total = $slots->count();
        $index = max(0, min($this->current, $total - 1));

        $overview = $slots->map(fn ($slot, $i) => (object) [
            'index' => $i,
            'question' => $slot->question,
            'answered' => $this->isAnswered($slot->question->id),
        ])->values();

        $slot = $slots->get($index);
        $question = $slot->question;
        $order = $slot->option_order ?: $question->options->pluck('id')->all();

        $options = collect($order)
            ->map(fn ($id) => $question->options->firstWhere('id', $id))
            ->filter()
            ->values();

        return view('livewire.quizzes.take', [
            'mode' => $this->reviewing ? 'review' : 'taking',
            'subject' => $subject,
            'total' => $total,
            'index' => $index,
            'overview' => $overview,
            'question' => $question,
            'options' => $options,
            'answeredCount' => $overview->where('answered', true)->count(),
            'secondsLeft' => $attempt->secondsLeft(),
        ]);
    }
}
