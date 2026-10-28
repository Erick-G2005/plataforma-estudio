<?php

namespace App\Livewire\Admin\Subjects;

use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    public int $order = 0;
    public $cover = null;
    public bool $removeCover = false;

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(Subject $subject)
    {
        $this->resetForm();
        $this->editing = $subject;
        $this->name = $subject->name;
        $this->description = $subject->description ?? '';
        $this->order = $subject->order;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'cover' => 'nullable|image|max:2048',
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
            'order' => $this->order,
            'cover_path' => $coverPath,
        ];

        if ($this->editing) {
            $this->editing->update($data);
        } else {
            Subject::create($data + ['slug' => Str::slug($this->name) . '-' . uniqid()]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Subject $subject)
    {
        if ($subject->cover_path) {
            Storage::disk('public')->delete($subject->cover_path);
        }

        $subject->delete();
    }

    protected function resetForm(): void
    {
        $this->reset(['editing', 'name', 'description', 'order', 'cover', 'removeCover']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.subjects.manage', [
            'subjects' => Subject::withCount('topics')->orderBy('order')->get(),
        ]);
    }
}