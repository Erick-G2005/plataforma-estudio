<?php

namespace App\Livewire\Admin\Quizzes;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\QuizService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Edita el quiz de un tema (/admin/topics/{topic}/quiz) o el examen final
 * de una materia (/admin/subjects/{subject}/exam): reglas y lista de preguntas.
 */
#[Layout('layouts.app')]
class Manage extends Component
{
    #[Locked]
    public ?int $topicId = null;

    #[Locked]
    public ?int $subjectId = null;

    #[Locked]
    public ?int $quizId = null;

    // Crear el quiz
    public string $newTitle = '';
    public string $newDrawn = '10';

    // Reglas (texto para que un campo vacío signifique "sin límite")
    public string $quizTitle = '';
    public string $passScore = '70';
    public string $maxAttempts = '';
    public string $cooldownHours = '24';
    public string $timeLimit = '';
    public string $perAttempt = '';
    public string $drawnCount = '0';
    public string $showAnswers = 'after_attempt';

    public function mount(?string $topic = null, ?string $subject = null)
    {
        if ($topic) {
            $topicModel = Topic::findOrFail($topic);
            $this->topicId = $topicModel->id;
            $this->newTitle = 'Quiz: ' . $topicModel->title;
            $quiz = $topicModel->quiz;
        } elseif ($subject) {
            $subjectModel = Subject::findOrFail($subject);
            $this->subjectId = $subjectModel->id;
            $this->newTitle = 'Examen final: ' . $subjectModel->name;
            $quiz = $subjectModel->finalExam;
        } else {
            abort(404);
        }

        if ($quiz) {
            $this->quizId = $quiz->id;
            $this->fillRules($quiz);
        }
    }

    public function createQuiz(): void
    {
        if ($this->quiz()) {
            return;
        }

        $rules = ['newTitle' => 'required|string|max:255'];

        if ($this->isFinal()) {
            $rules['newDrawn'] = 'required|integer|min:0|max:500';
        }

        $this->validate($rules, [], [
            'newTitle' => 'título',
            'newDrawn' => 'preguntas sorteadas',
        ]);

        $quiz = $this->isFinal()
            ? Quiz::create([
                'type' => Quiz::TYPE_FINAL,
                'subject_id' => $this->subjectId,
                'title' => $this->newTitle,
                'drawn_questions_count' => (int) $this->newDrawn,
            ])
            : Quiz::create([
                'type' => Quiz::TYPE_TOPIC,
                'topic_id' => $this->topicId,
                'title' => $this->newTitle,
            ]);

        // Relee el quiz: así trae los valores por defecto que pone la base de datos
        $quiz->refresh();

        $this->quizId = $quiz->id;
        $this->fillRules($quiz);
    }

    public function saveRules(): void
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return;
        }

        $rules = [
            'quizTitle' => 'required|string|max:255',
            'passScore' => 'required|integer|min:1|max:100',
            'maxAttempts' => 'nullable|integer|min:1|max:1000',
            'cooldownHours' => 'required|integer|min:0|max:720',
            'timeLimit' => 'nullable|integer|min:1|max:600',
            'perAttempt' => 'nullable|integer|min:1|max:500',
            'showAnswers' => 'required|in:never,after_pass,after_attempt',
        ];

        if ($this->isFinal()) {
            $rules['drawnCount'] = 'required|integer|min:0|max:500';
        }

        $this->validate($rules, [], [
            'quizTitle' => 'título',
            'passScore' => 'nota mínima',
            'maxAttempts' => 'intentos máximos',
            'cooldownHours' => 'espera',
            'timeLimit' => 'tiempo límite',
            'perAttempt' => 'preguntas por intento',
            'drawnCount' => 'preguntas sorteadas',
            'showAnswers' => 'visibilidad de respuestas',
        ]);

        $quiz->update([
            'title' => $this->quizTitle,
            'pass_score' => (int) $this->passScore,
            'max_attempts' => $this->maxAttempts === '' ? null : (int) $this->maxAttempts,
            'cooldown_hours' => (int) $this->cooldownHours,
            'time_limit_minutes' => $this->timeLimit === '' ? null : (int) $this->timeLimit,
            'questions_per_attempt' => $this->perAttempt === '' ? null : (int) $this->perAttempt,
            'drawn_questions_count' => $this->isFinal() ? (int) $this->drawnCount : 0,
            'show_answers' => $this->showAnswers,
        ]);

        $this->dispatch('rules-saved');
    }

    /**
     * Recibe el nuevo orden (ids de pregunta) después de arrastrar.
     */
    public function reorder(array $ids): void
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return;
        }

        $valid = $quiz->questions()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $position = 1;

        foreach ($ids as $id) {
            $id = (int) $id;

            if (in_array($id, $valid, true)) {
                Question::where('id', $id)->update(['order' => $position++]);
            }
        }
    }

    public function move(int $questionId, string $direction): void
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return;
        }

        $ids = $quiz->questions()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $index = array_search($questionId, $ids, true);

        if ($index === false) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($target < 0 || $target >= count($ids)) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

        $this->reorder($ids);
    }

    public function duplicateQuestion(int $id): void
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return;
        }

        $original = Question::where('quiz_id', $quiz->id)->with('options')->find($id);

        if (! $original) {
            return;
        }

        $copy = $original->replicate(['image_path']);
        $copy->statement = $original->statement . ' (copia)';
        $copy->order = (Question::where('quiz_id', $quiz->id)->max('order') ?? 0) + 1;
        $copy->image_path = $this->copyImage($original->image_path);
        $copy->save();

        foreach ($original->options as $option) {
            $copy->options()->create([
                'text' => $option->text,
                'is_correct' => $option->is_correct,
            ]);
        }
    }

    public function deleteQuestion(int $id): void
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return;
        }

        $question = Question::where('quiz_id', $quiz->id)->find($id);

        if (! $question) {
            return;
        }

        if ($question->image_path) {
            Storage::disk('public')->delete($question->image_path);
        }

        $question->delete();
    }

    public function deleteQuiz(): void
    {
        $quiz = $this->quiz();

        if (! $quiz) {
            return;
        }

        foreach ($quiz->questions()->whereNotNull('image_path')->pluck('image_path') as $path) {
            Storage::disk('public')->delete($path);
        }

        $quiz->delete();
        $this->quizId = null;
    }

    // ───────────────────────── internos ─────────────────────────

    protected function isFinal(): bool
    {
        return $this->subjectId !== null;
    }

    protected function quiz(): ?Quiz
    {
        return $this->quizId ? Quiz::find($this->quizId) : null;
    }

    protected function fillRules(Quiz $quiz): void
    {
        $this->quizTitle = $quiz->title;
        $this->passScore = (string) ($quiz->pass_score ?? 70);
        $this->maxAttempts = $quiz->max_attempts ? (string) $quiz->max_attempts : '';
        $this->cooldownHours = (string) ($quiz->cooldown_hours ?? 24);
        $this->timeLimit = $quiz->time_limit_minutes ? (string) $quiz->time_limit_minutes : '';
        $this->perAttempt = $quiz->questions_per_attempt ? (string) $quiz->questions_per_attempt : '';
        $this->drawnCount = (string) ($quiz->drawn_questions_count ?? 0);
        $this->showAnswers = $quiz->show_answers ?? 'after_attempt';
    }

    /**
     * Copia el archivo de la imagen para que la pregunta duplicada tenga el suyo propio.
     */
    protected function copyImage(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $newPath = 'questions/' . Str::uuid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
        Storage::disk('public')->copy($path, $newPath);

        return $newPath;
    }

    public function render()
    {
        $quiz = $this->quiz();
        $topic = $this->topicId ? Topic::find($this->topicId) : null;
        $subject = $this->isFinal() ? Subject::find($this->subjectId) : $topic?->subject;

        $questions = $quiz ? $quiz->questions()->withCount('options')->get() : collect();

        // Preguntas disponibles en los quizzes de los temas (solo para el examen final)
        $pool = 0;

        if ($this->isFinal() && $subject) {
            $quizIds = Quiz::whereIn('topic_id', $subject->topics()->pluck('id'))->pluck('id');
            $pool = Question::whereIn('quiz_id', $quizIds)->count();
        }

        return view('livewire.admin.quizzes.manage', [
            'quiz' => $quiz,
            'topic' => $topic,
            'subject' => $subject,
            'isFinal' => $this->isFinal(),
            'questions' => $questions,
            'pool' => $pool,
            'expected' => $quiz ? app(QuizService::class)->expectedQuestionCount($quiz) : 0,
        ]);
    }
}
