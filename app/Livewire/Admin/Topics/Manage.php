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
    public int $order = 0;

    public function mount(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function openCreate()
    {
        $this->reset(['editing', 'title', 'description', 'order']);
        $this->showModal = true;
    }

    public function openEdit(Topic $topic)
    {
        $this->editing = $topic;
        $this->title = $topic->title;
        $this->description = $topic->description ?? '';
        $this->order = $topic->order;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            $this->subject->topics()->create($validated);
        }

        $this->showModal = false;
        $this->reset(['editing', 'title', 'description', 'order']);
    }

    public function delete(Topic $topic)
    {
        $topic->delete();
    }

    public function render()
    {
        return view('livewire.admin.topics.manage', [
            'topics' => $this->subject->topics()
                ->withCount('materials')
                ->with(['quiz' => fn ($q) => $q->withCount('questions')])
                ->orderBy('order')
                ->get(),
            'exam' => $this->subject->finalExam()->withCount('questions')->first(),
        ]);
    }
}
