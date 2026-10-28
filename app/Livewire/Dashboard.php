<?php

namespace App\Livewire;

use App\Models\Subject;
use App\Services\CourseProgress;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $enrolled = app(CourseProgress::class)->enrolledOverview(auth()->user());

        $subjects = Subject::withCount(['topics', 'students'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('order')
            ->paginate(9);

        return view('livewire.dashboard', [
            'subjects' => $subjects,
            'enrolledIds' => $enrolled->pluck('id')->all(),
            // Solo las materias que todavía tienen un siguiente paso
            'continueLearning' => $enrolled->filter(fn ($s) => $s->next_step)->take(3),
        ]);
    }
}
