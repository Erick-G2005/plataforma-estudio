<?php

namespace App\Livewire\Admin\Topics;

use App\Models\Subject;
use App\Models\Topic;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Manage extends Component
{
    public Subject $subject;

    public bool $showModal = false;
    public ?Topic $editing = null;

    public string $title = '';
    public string $description = '';

    public function mount(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function openCreate(): void
    {
        $this->reset(['editing', 'title', 'description']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $topicId): void
    {
        $topic = $this->subject->topics()->findOrFail($topicId);

        $this->resetValidation();
        $this->editing = $topic;
        $this->title = $topic->title;
        $this->description = $topic->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [], [
            'title' => 'título',
            'description' => 'descripción',
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            // Los temas nuevos van al final de la lista
            $this->subject->topics()->create($validated + [
                'order' => (Topic::where('subject_id', $this->subject->id)->max('order') ?? 0) + 1,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editing', 'title', 'description']);
    }

    public function delete(int $topicId): void
    {
        $this->subject->topics()->where('id', $topicId)->first()?->delete();
    }

    /**
     * Recibe el nuevo orden (ids de tema) después de arrastrar.
     */
    public function reorder(array $ids): void
    {
        $valid = $this->subject->topics()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $position = 1;

        foreach ($ids as $id) {
            $id = (int) $id;

            if (in_array($id, $valid, true)) {
                Topic::where('id', $id)->update(['order' => $position++]);
            }
        }
    }

    public function move(int $topicId, string $direction): void
    {
        $ids = $this->subject->topics()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $index = array_search($topicId, $ids, true);

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

    public function render()
    {
        return view('livewire.admin.topics.manage', [
            'topics' => $this->subject->topics()
                ->withCount('materials')
                ->with(['quiz' => fn ($q) => $q->withCount('questions')])
                ->get(),
            'exam' => $this->subject->finalExam()->withCount('questions')->first(),
        ]);
    }
}
