<?php

namespace App\Livewire\Admin\Subjects;

use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Manage extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public ?Subject $editing = null;

    public string $name = '';
    public string $description = '';
    public $cover = null;
    public bool $removeCover = false;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $subjectId): void
    {
        $subject = Subject::findOrFail($subjectId);

        $this->resetForm();
        $this->editing = $subject;
        $this->name = $subject->name;
        $this->description = $subject->description ?? '';
        $this->showModal = true;
    }

    public function updatedCover(): void
    {
        try {
            $this->validateOnly('cover', ['cover' => 'nullable|image|max:2048'], [], ['cover' => 'portada']);
        } catch (ValidationException $e) {
            $this->cover = null;

            throw $e;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover' => 'nullable|image|max:2048',
        ], [], [
            'name' => 'nombre',
            'description' => 'descripción',
            'cover' => 'portada',
        ]);

        $coverPath = $this->editing?->cover_path;

        if ($this->removeCover && $coverPath) {
            Storage::disk('public')->delete($coverPath);
            $coverPath = null;
        }

        if ($this->cover) {
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            $coverPath = $this->cover->store('covers', 'public');
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'cover_path' => $coverPath,
        ];

        if ($this->editing) {
            $this->editing->update($data);
        } else {
            // Las materias nuevas van al final de la lista
            Subject::create($data + [
                'slug' => Str::slug($this->name) . '-' . uniqid(),
                'order' => (Subject::max('order') ?? 0) + 1,
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $subjectId): void
    {
        $subject = Subject::find($subjectId);

        if (! $subject) {
            return;
        }

        if ($subject->cover_path) {
            Storage::disk('public')->delete($subject->cover_path);
        }

        $subject->delete();
    }

    /**
     * Recibe el nuevo orden (ids de materia) después de arrastrar.
     */
    public function reorder(array $ids): void
    {
        $valid = Subject::pluck('id')->map(fn ($id) => (int) $id)->all();
        $position = 1;

        foreach ($ids as $id) {
            $id = (int) $id;

            if (in_array($id, $valid, true)) {
                Subject::where('id', $id)->update(['order' => $position++]);
            }
        }
    }

    public function move(int $subjectId, string $direction): void
    {
        $ids = Subject::orderBy('order')->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $index = array_search($subjectId, $ids, true);

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

    protected function resetForm(): void
    {
        $this->reset(['editing', 'name', 'description', 'cover', 'removeCover']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.subjects.manage', [
            'subjects' => Subject::withCount('topics')->orderBy('order')->get(),
        ]);
    }
}
