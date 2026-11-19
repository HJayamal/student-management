<?php

namespace App\Http\Controllers;

use App\Models\LectureMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LectureMaterialController extends Controller
{
    private function assertTeacher(Request $request): void
    {
        $user = $request->user();
        $table = $user && method_exists($user, 'getTable') ? $user->getTable() : '';
        $role = strtolower((string) ($user->role ?? ''));

        abort_unless($user && ($role === 'teacher' || $table === 'teachers'), 403, 'Only teacher accounts can manage lecture materials.');
    }

    private function assertStudent(Request $request): void
    {
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? ''));

        abort_unless($user && $role === 'student', 403, 'Only student accounts can view shared lecture materials.');
    }

    private function present(LectureMaterial $material): array
    {
        return [
            'id' => $material->id,
            'title' => $material->title,
            'description' => $material->description,
            'material_type' => $material->material_type,
            'teacher_name' => $material->teacher_name,
            'original_filename' => $material->original_filename,
            'mime_type' => $material->mime_type,
            'file_size' => $material->file_size,
            'external_url' => $material->external_url,
            'download_url' => $material->file_path
                ? URL::temporarySignedRoute(
                    'lecture-materials.download',
                    now()->addHours(12),
                    ['material' => $material->id]
                )
                : null,
            'created_at' => $material->created_at,
        ];
    }

    public function teacherIndex(Request $request)
    {
        $this->assertTeacher($request);
        $teacherId = $request->user()->getAuthIdentifier();

        $materials = LectureMaterial::query()
            ->where('teacher_id', $teacherId)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (LectureMaterial $material) => $this->present($material));

        return response()->json(['materials' => $materials]);
    }

    public function studentIndex(Request $request)
    {
        $this->assertStudent($request);

        $materials = LectureMaterial::query()
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (LectureMaterial $material) => $this->present($material));

        return response()->json(['materials' => $materials]);
    }

    public function store(Request $request)
    {
        $this->assertTeacher($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,doc,docx,ppt,pptx'],
            'external_url' => ['nullable', 'url', 'max:2048', 'regex:/^https?:\/\//i'],
        ]);

        $file = $request->file('file');
        $externalUrl = trim((string) ($validated['external_url'] ?? ''));

        // A lecture material must contain at least one resource. A document
        // and an optional external link can be stored on the same record.
        if (!$file && $externalUrl === '') {
            return response()->json([
                'message' => 'Choose a document file, add an external link, or provide both.',
            ], 422);
        }

        $user = $request->user();
        $data = [
            'teacher_id' => $user->getAuthIdentifier(),
            'teacher_name' => $user->name ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'material_type' => $file ? 'file' : 'link',
        ];

        if ($file) {
            $path = $file->store('lecture-materials', 'local');
            $data['file_path'] = $path;
            $data['original_filename'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType() ?: $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
        }

        if ($externalUrl !== '') {
            $data['external_url'] = $externalUrl;
        }

        $material = LectureMaterial::create($data);

        return response()->json([
            'message' => 'Lecture material shared with students successfully.',
            'material' => $this->present($material),
        ], 201);
    }

    public function destroy(Request $request, LectureMaterial $material)
    {
        $this->assertTeacher($request);
        abort_unless((string) $material->teacher_id === (string) $request->user()->getAuthIdentifier(), 403, 'You can only delete materials that you shared.');

        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
        }

        $material->delete();

        return response()->json(['message' => 'Lecture material deleted successfully.']);
    }

    public function download(LectureMaterial $material)
    {
        abort_unless($material->file_path, 404);
        abort_unless(Storage::disk('local')->exists($material->file_path), 404, 'The material file could not be found.');

        return Storage::disk('local')->download(
            $material->file_path,
            $material->original_filename ?: basename($material->file_path),
            $material->mime_type ? ['Content-Type' => $material->mime_type] : []
        );
    }
}
