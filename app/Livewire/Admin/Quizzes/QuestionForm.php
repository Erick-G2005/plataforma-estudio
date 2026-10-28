<?php

namespace App\Livewire\Admin\Quizzes;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Crear (/admin/quizzes/{quiz}/questions/create) o editar
 * (/admin/quizzes/{quiz}/questions/{question}/edit) una pregunta.
 */
#[Layout('layouts.app')]
class QuestionForm extends Component
{
    use WithFileUploads;

    public Quiz $quiz;

    #[Locked]
    public ?int $questionId = null;

    public string $statement = '';
    public string $type = 'single_choice';
    public ?string $explanation = null;
    public ?string $correctAnswerText = null;
    public $image = null;
    public bool $removeImage = false;
    public array $options = [];
    public ?int $correctOptionIndex = null;

    public function mount(Quiz $quiz, ?string $question = null)
    {
        $this->quiz = $quiz;

        if ($question === null) {
            $this->options = [
                ['text' => '', 'is_correct' => false],
                ['text' => '', 'is_correct' => false],
            ];

            return;
        }

        $model = Question::where('quiz_id', $quiz->id)->with('options')->findOrFail($question);

        $this->questionId = $model->id;
        $this->statement = $model->statement;
        $this->type = $model->type;
        $this->explanation = $model->explanation;
        $this->correctAnswerText = $model->correct_answer_text;

        $this->options = $model->options->map(fn ($o) => [
            'text' => $o->text,
            'is_correct' => (bool) $o->is_correct,
        ])->values()->all();

        if (in_array($model->type, ['single_choice', 'true_false'])) {
            $found = collect($this->options)->search(fn ($o) => $o['is_correct']);
            $this->correctOptionIndex = $found === false ? null : $found;
        }
    }

    public function updatedType($value): void
    {
        $this->correctOptionIndex = null;

        if ($value === 'true_false') {
            $this->options = [
                ['text' => 'Verdadero', 'is_correct' => false],
                ['text' => 'Falso', 'is_correct' => false],
            ];
        } elseif ($value === 'short_answer') {
            $this->options = [];
        } elseif (count($this->options) < 2 || $this->options[0]['text'] === 'Verdadero') {
            $this->options = [
                ['text' => '', 'is_correct' => false],
                ['text' => '', 'is_correct' => false],
            ];
        }
    }

    public function updatedImage(): void
    {
        try {
            $this->validateOnly('image', ['image' => 'nullable|image|max:2048'], [], ['image' => 'imagen']);
        } catch (ValidationException $e) {
            $this->image = null;

            throw $e;
        }
    }

    public function addOption(): void
    {
        $this->options[] = ['text' => '', 'is_correct' => false];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);

        // Mantiene bien marcada la opción correcta tras quitar una
        if ($this->correctOptionIndex !== null) {
            if ($this->correctOptionIndex === $index) {
                $this->correctOptionIndex = null;
            } elseif ($this->correctOptionIndex > $index) {
                $this->correctOptionIndex--;
            }
        }
    }

    public function save(bool $another = false)
    {
        $rules = [
            'statement' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,true_false,short_answer',
            'explanation' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ];

        if ($this->type === 'short_answer') {
            $rules['correctAnswerText'] = 'required|string';
        } else {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*.text'] = 'required|string';
        }

        $this->validate($rules, [], [
            'statement' => 'enunciado',
            'explanation' => 'explicación',
            'correctAnswerText' => 'respuesta correcta',
            'options' => 'opciones',
            'options.*.text' => 'texto de la opción',
            'image' => 'imagen',
        ]);

        if (in_array($this->type, ['single_choice', 'true_false'])) {
            if ($this->correctOptionIndex === null) {
                $this->addError('correctOptionIndex', 'Selecciona cuál opción es la correcta.');

                return;
            }

            foreach ($this->options as $i => $option) {
                $this->options[$i]['is_correct'] = ((int) $i === (int) $this->correctOptionIndex);
            }
        } elseif ($this->type === 'multiple_choice') {
            if (collect($this->options)->where('is_correct', true)->isEmpty()) {
                $this->addError('options', 'Marca al menos una opción como correcta.');

                return;
            }
        }

        $question = $this->questionId
            ? Question::where('quiz_id', $this->quiz->id)->findOrFail($this->questionId)
            : null;

        $imagePath = $question?->image_path;

        if ($this->removeImage && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($this->image) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $this->image->store('questions', 'public');
        }

        $data = [
            'statement' => $this->statement,
            'type' => $this->type,
            'explanation' => $this->explanation ?: null,
            'image_path' => $imagePath,
            'correct_answer_text' => $this->type === 'short_answer' ? $this->correctAnswerText : null,
            'order' => $question?->order ?? ((Question::where('quiz_id', $this->quiz->id)->max('order') ?? 0) + 1),
        ];

        if ($question) {
            $question->update($data);
            $question->options()->delete();
        } else {
            $question = $this->quiz->questions()->create($data);
        }

        if ($this->type !== 'short_answer') {
            foreach ($this->options as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                ]);
            }
        }

        $this->redirect(
            $another ? route('admin.quizzes.questions.create', $this->quiz) : $this->quiz->adminUrl(),
            navigate: true
        );
    }

    public function render()
    {
        $existing = $this->questionId ? Question::find($this->questionId) : null;

        return view('livewire.admin.quizzes.question-form', [
            'existingImageUrl' => $existing?->image_path
                ? Storage::disk('public')->url($existing->image_path)
                : null,
        ]);
    }
}
