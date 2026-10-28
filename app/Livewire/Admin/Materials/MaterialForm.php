<?php

namespace App\Livewire\Admin\Materials;

use App\Models\Material;
use App\Models\Topic;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Crear (/admin/topics/{topic}/materials/create) o editar
 * (/admin/materials/{material}/edit) un material.
 */
#[Layout('layouts.app')]
class MaterialForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $topicId = null;

    #[Locked]
    public ?int $materialId = null;

    public string $type = 'text';
    public string $title = '';

    // Texto en formato Markdown, descripción de la imagen o enlace, según el tipo
    public string $content = '';

    public string $pdfSource = 'upload'; // upload | url
    public $file = null;

    public function mount(?string $topic = null, ?string $material = null)
    {
        if ($material !== null) {
            $model = Material::findOrFail($material);

            $this->materialId = $model->id;
            $this->topicId = $model->topic_id;
            $this->type = $model->type;
            $this->title = $model->title;
            $this->content = (string) $model->content;
            $this->pdfSource = $model->file_path ? 'upload' : 'url';

            return;
        }

        $this->topicId = Topic::findOrFail($topic)->id;
    }

    /**
     * Al cambiar de tipo se limpia lo que no aplica al nuevo tipo.
     */
    public function updatingType($value): void
    {
        if ($this->family($this->type) !== $this->family((string) $value)) {
            $this->content = '';
        }

        $this->file = null;
        $this->resetValidation();
    }

    public function updatedPdfSource(): void
    {
        $this->file = null;
        $this->resetValidation();
    }

    public function updatedFile(): void
    {
        try {
            $this->validateOnly('file', ['file' => $this->fileRules(true)], [], ['file' => 'archivo']);
        } catch (ValidationException $e) {
            $this->file = null;

            throw $e;
        }
    }

    public function save(bool $another = false)
    {
        $existing = $this->materialId ? Material::findOrFail($this->materialId) : null;
        $usesFile = $this->usesFile();

        // Se conserva el archivo ya subido solo si sigue siendo del mismo tipo
        $keepsExistingFile = $usesFile
            && $existing
            && $existing->file_path
            && $existing->type === $this->type;

        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,image,pdf,video,link',
        ];

        if ($this->type === 'text') {
            $rules['content'] = 'required|string';
        } elseif ($this->type === 'image') {
            $rules['content'] = 'nullable|string|max:255';
            $rules['file'] = $this->fileRules($keepsExistingFile);
        } elseif ($this->type === 'pdf' && $this->pdfSource === 'upload') {
            $rules['file'] = $this->fileRules($keepsExistingFile);
        } else {
            $rules['content'] = 'required|url:http,https|max:2048';
        }

        $this->validate($rules, [], [
            'title' => 'título',
            'type' => 'tipo',
            'content' => match ($this->type) {
                'text' => 'contenido',
                'image' => 'descripción',
                default => 'enlace',
            },
            'file' => 'archivo',
        ]);

        $oldPath = $existing?->file_path;
        $path = null;
        $name = null;

        if ($usesFile) {
            if ($this->file) {
                $path = $this->file->store('materials', 'public');
                $name = $this->file->getClientOriginalName();
            } elseif ($keepsExistingFile) {
                $path = $existing->file_path;
                $name = $existing->file_name;
            }
        }

        // Si el archivo anterior ya no se usa (se reemplazó o cambió el tipo), se borra
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        $content = match (true) {
            $this->type === 'image' => $this->content !== '' ? $this->content : null,
            $this->type === 'pdf' && $this->pdfSource === 'upload' => null,
            default => $this->content,
        };

        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'content' => $content,
            'file_path' => $path,
            'file_name' => $name,
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            $data['order'] = (Material::where('topic_id', $this->topicId)->max('order') ?? 0) + 1;
            Material::create($data + ['topic_id' => $this->topicId]);
        }

        $this->redirect(
            $another
                ? route('admin.topics.materials.create', $this->topicId)
                : route('admin.topics.materials', $this->topicId),
            navigate: true
        );
    }

    // ───────────────────────── internos ─────────────────────────

    protected function usesFile(): bool
    {
        return $this->type === 'image'
            || ($this->type === 'pdf' && $this->pdfSource === 'upload');
    }

    /**
     * Familia del contenido: el texto, la descripción de una imagen y los enlaces
     * no se pueden reutilizar entre sí.
     */
    protected function family(string $type): string
    {
        return match ($type) {
            'text' => 'text',
            'image' => 'caption',
            default => 'url',
        };
    }

    protected function fileRules(bool $nullable = false): array
    {
        $presence = $nullable ? 'nullable' : 'required';

        return $this->type === 'image'
            ? [$presence, 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096']
            : [$presence, 'file', 'mimes:pdf', 'max:10240'];
    }

    /**
     * El material tal como lo verá el estudiante, con lo que hay escrito en el formulario.
     */
    protected function previewMaterial(?Material $existing): object
    {
        $fileUrl = null;
        $fileName = null;
        $pending = false;

        if ($this->usesFile()) {
            if ($this->file) {
                $fileName = $this->file->getClientOriginalName();

                if ($this->type === 'image' && $this->file->isPreviewable()) {
                    $fileUrl = $this->file->temporaryUrl();
                } else {
                    $pending = true;
                }
            } elseif ($existing && $existing->file_path && $existing->type === $this->type) {
                $fileUrl = Storage::disk('public')->url($existing->file_path);
                $fileName = $existing->file_name;
            }
        }

        return (object) [
            'type' => $this->type,
            'title' => $this->title !== '' ? $this->title : 'Título del material',
            'content' => ($this->type === 'pdf' && $this->pdfSource === 'upload') ? '' : $this->content,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'pending_upload' => $pending,
        ];
    }

    public function render()
    {
        $topic = Topic::with('subject')->findOrFail($this->topicId);
        $existing = $this->materialId ? Material::find($this->materialId) : null;

        $existingFile = ($existing && $existing->file_path)
            ? (object) [
                'type' => $existing->type,
                'url' => Storage::disk('public')->url($existing->file_path),
                'name' => $existing->file_name,
            ]
            : null;

        return view('livewire.admin.materials.material-form', [
            'topic' => $topic,
            'existingFile' => $existingFile,
            'preview' => $this->previewMaterial($existing),
        ]);
    }
}
