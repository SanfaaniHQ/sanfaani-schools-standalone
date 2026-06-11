<?php

namespace App\Services\Lms;

use App\Models\LmsMaterial;
use App\Models\LmsResource;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LmsResourceStorageService
{
    private const PRIVATE_DISKS = ['local'];

    public function __construct(
        private LmsAccessService $access,
        private AuditLogService $audit,
    ) {}

    public function validationRules(): array
    {
        return [
            'resource' => [
                'required',
                'file',
                'max:'.($this->maxUploadMb() * 1024),
                'mimes:'.implode(',', $this->allowedExtensions()),
                'mimetypes:'.implode(',', $this->allowedMimeTypes()),
            ],
        ];
    }

    public function store(School $school, User $actor, LmsMaterial $material, UploadedFile $file): LmsResource
    {
        abort_unless($this->access->canManageMaterial($actor, $school, $material), 403);

        $this->assertPrivateDisk($this->disk());
        $this->assertAllowedFile($file);

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = 'lms/schools/'.$school->id.'/materials/'.$material->id;
        $path = $file->storeAs($directory, $filename, $this->disk());

        if (! $path || ! Storage::disk($this->disk())->exists($path)) {
            throw ValidationException::withMessages([
                'resource' => 'The LMS resource could not be stored securely.',
            ]);
        }

        $resource = LmsResource::create([
            'school_id' => $school->id,
            'lms_material_id' => $material->id,
            'disk' => $this->disk(),
            'path' => $path,
            'original_name' => $this->safeOriginalName($file),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => (int) $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => $actor->id,
            'status' => LmsResource::STATUS_ACTIVE,
        ]);

        $this->audit->log('lms_resource_uploaded', $resource, $school, metadata: $this->resourceMetadata($resource, $actor));

        return $resource;
    }

    public function download(School $school, User $actor, LmsResource $resource): StreamedResponse
    {
        abort_unless($this->access->canDownloadResource($actor, $school, $resource), 403);
        abort_unless(in_array($resource->disk, self::PRIVATE_DISKS, true), 404);
        abort_unless(Storage::disk($resource->disk)->exists($resource->path), 404);

        $this->audit->log('lms_resource_downloaded', $resource, $school, metadata: $this->resourceMetadata($resource, $actor));

        return Storage::disk($resource->disk)->download($resource->path, $resource->original_name);
    }

    public function allowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'webp'];
    }

    public function allowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
    }

    public function maxUploadMb(): int
    {
        return (int) config('lms.max_upload_mb', 10);
    }

    public function disk(): string
    {
        return (string) config('lms.resource_disk', 'local');
    }

    public function malwareScanningEnabled(): bool
    {
        return (bool) config('lms.malware_scanning.enabled', false);
    }

    private function assertPrivateDisk(string $disk): void
    {
        if (! in_array($disk, self::PRIVATE_DISKS, true)) {
            throw ValidationException::withMessages([
                'resource' => 'LMS resources must be stored on a private disk.',
            ]);
        }
    }

    private function assertAllowedFile(UploadedFile $file): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mime = (string) ($file->getMimeType() ?: $file->getClientMimeType());

        if (! in_array($extension, $this->allowedExtensions(), true) || ! in_array($mime, $this->allowedMimeTypes(), true)) {
            throw ValidationException::withMessages([
                'resource' => 'The LMS resource type is not allowed.',
            ]);
        }

        if (in_array($extension, ['php', 'phtml', 'phar', 'js', 'html', 'htm', 'exe', 'bat', 'cmd', 'sh'], true)) {
            throw ValidationException::withMessages([
                'resource' => 'Executable or script files are not allowed as LMS resources.',
            ]);
        }
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return Str::limit(Str::slug($name, '-') ?: 'resource', 120, '').'.'.$extension;
    }

    private function resourceMetadata(LmsResource $resource, User $actor): array
    {
        $material = $resource->relationLoaded('material')
            ? $resource->material
            : $resource->material()->first();
        $classroom = $material
            ? ($material->relationLoaded('classroom') ? $material->classroom : $material->classroom()->first())
            : null;

        return [
            'school_id' => $resource->school_id,
            'classroom_id' => $material?->lms_classroom_id,
            'material_id' => $resource->lms_material_id,
            'resource_id' => $resource->id,
            'class_id' => $classroom?->school_class_id,
            'subject_id' => $classroom?->subject_id,
            'session_id' => $classroom?->academic_session_id,
            'term_id' => $classroom?->term_id,
            'status' => $resource->status,
            'actor_id' => $actor->id,
            'size' => $resource->size,
            'mime_type' => $resource->mime_type,
            'extension' => $resource->extension,
        ];
    }
}
