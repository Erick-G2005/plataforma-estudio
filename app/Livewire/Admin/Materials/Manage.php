<?php

namespace App\Livewire\Admin\Materials;

use App\Models\Material;
use App\Models\Topic;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Manage extends Component
{
    public Topic $topic;

    public function mount(Topic $topic)
    {
        $this->topic = $topic;
    }

    /**
     * Recibe el nuevo orden (ids de material) después de arrastrar.
     */
    public function reorder(array $ids): void
    {
        $valid = $this->topic->materials()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $position = 1;

        foreach ($ids as $id) {
            $id = (int) $id;

            if (in_array($id, $valid, true)) {
                Material::where('id', $id)->update(['order' => $position++]);
            }
        }
    }

    public function move(int $materialId, string $direction): void
    {
        $ids = $this->topic->materials()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $index = array_search($materialId, $ids, true);

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

    public function duplicate(int $id): void
    {
        $original = Material::where('topic_id', $this->topic->id)->find($id);

        if (! $original) {
            return;
        }

        $copy = $original->replicate(['file_path']);
        $copy->title = $original->title . ' (copia)';
        $copy->order = (Material::where('topic_id', $this->topic->id)->max('order') ?? 0) + 1;
        $copy->file_path = $this->copyFile($original->file_path);
        $copy->save();
    }

    public function delete(int $id): void
    {
        $material = Material::where('topic_id', $this->topic->id)->find($id);

        if (! $material) {
            return;
        }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();
    }

    /**
     * Copia el archivo para que el material duplicado tenga el suyo propio.
     */
    protected function copyFile(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $newPath = 'materials/' . Str::uuid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
        Storage::disk('public')->copy($path, $newPath);

        return $newPath;
    }

    public function render()
    {
        return view('livewire.admin.materials.manage', [
            'materials' => $this->topic->materials()->get(),
        ]);
    }
}
