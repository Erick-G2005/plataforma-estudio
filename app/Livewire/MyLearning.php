<?php

namespace App\Livewire;

use App\Services\CourseProgress;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MyLearning extends Component
{
    public function render()
    {
        // Las materias en curso primero, las terminadas al final
        $subjects = app(CourseProgress::class)
            ->enrolledOverview(auth()->user())
            ->sortBy(fn ($s) => $s->completed)
            ->values();

        return view('livewire.my-learning', [
            'subjects' => $subjects,
            'topicsCompleted' => $subjects->sum('completed_topics'),
            'finishedCount' => $subjects->where('completed', true)->count(),
        ]);
    }
}
