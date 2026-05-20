<?php

namespace App\Services;

use App\Models\PdfSnapshot;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class PdfSnapshotService
{
    public function capture(
        string $snapshotType,
        string $title,
        array $payload,
        ?School $school = null,
        ?Model $subject = null,
        ?Model $owner = null,
        ?User $generatedBy = null,
        ?string $referenceCode = null,
        ?string $locale = null,
        array $metadata = []
    ): PdfSnapshot {
        $locale ??= app()->getLocale();
        $direction = in_array($locale, config('sanfaani.rtl_locales', ['ar']), true) ? 'rtl' : 'ltr';
        $branding = $this->brandingSnapshot($school);
        $hashPayload = [
            'snapshot_type' => $snapshotType,
            'title' => $title,
            'payload' => $payload,
            'branding' => $branding,
            'locale' => $locale,
            'direction' => $direction,
            'reference_code' => $referenceCode,
        ];
        $snapshotHash = $this->hashPayload($hashPayload);

        return DB::transaction(function () use ($snapshotType, $title, $payload, $school, $subject, $owner, $generatedBy, $referenceCode, $locale, $direction, $metadata, $branding, $snapshotHash) {
            $existing = PdfSnapshot::where('snapshot_hash', $snapshotHash)->first();

            if ($existing) {
                return $existing;
            }

            $version = $this->nextVersion($snapshotType, $school, $subject, $referenceCode);

            return PdfSnapshot::create([
                'snapshot_uuid' => (string) Str::uuid(),
                'school_id' => $school?->id,
                'snapshot_type' => $snapshotType,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'owner_type' => $owner ? $owner::class : null,
                'owner_id' => $owner?->getKey(),
                'title' => $title,
                'reference_code' => $referenceCode,
                'snapshot_version' => $version,
                'payload_schema_version' => PdfSnapshot::PAYLOAD_SCHEMA_VERSION,
                'locale' => $locale,
                'direction' => $direction,
                'payload' => $payload,
                'branding_snapshot' => $branding,
                'snapshot_hash' => $snapshotHash,
                'verification_code' => $this->verificationCode(),
                'status' => 'active',
                'generated_by' => $generatedBy?->id,
                'generated_at' => now(),
                'metadata' => array_merge([
                    'immutable_payload' => true,
                    'renderer' => 'mpdf',
                ], $metadata),
            ]);
        });
    }

    public function generate(PdfSnapshot $snapshot): PdfSnapshot
    {
        $html = view('pdf.snapshot', ['snapshot' => $snapshot])->render();
        $directory = 'pdf-snapshots/'.now()->format('Y/m');
        $path = $directory.'/'.$snapshot->snapshot_uuid.'.pdf';
        $disk = config('sanfaani.pdf.disk', 'local');
        $tempDirectory = storage_path('app/mpdf-temp');

        File::ensureDirectoryExists($tempDirectory);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => $tempDirectory,
        ]);
        $mpdf->SetDirectionality($snapshot->direction === 'rtl' ? 'rtl' : 'ltr');
        $mpdf->SetWatermarkText(data_get($snapshot->branding_snapshot, 'watermark', 'SANFAANI'));
        $mpdf->showWatermarkText = true;
        $mpdf->WriteHTML($html);

        $bytes = $mpdf->Output('', 'S');
        Storage::disk($disk)->put($path, $bytes);

        $snapshot->update([
            'pdf_disk' => $disk,
            'pdf_path' => $path,
            'pdf_hash' => hash('sha256', $bytes),
            'pdf_generated_at' => now(),
        ]);

        return $snapshot->refresh();
    }

    public function captureAndGenerate(
        string $snapshotType,
        string $title,
        array $payload,
        ?School $school = null,
        ?Model $subject = null,
        ?Model $owner = null,
        ?User $generatedBy = null,
        ?string $referenceCode = null,
        ?string $locale = null,
        array $metadata = []
    ): PdfSnapshot {
        return $this->generate($this->capture(
            $snapshotType,
            $title,
            $payload,
            $school,
            $subject,
            $owner,
            $generatedBy,
            $referenceCode,
            $locale,
            $metadata
        ));
    }

    private function nextVersion(string $snapshotType, ?School $school, ?Model $subject, ?string $referenceCode): int
    {
        return ((int) PdfSnapshot::query()
            ->where('snapshot_type', $snapshotType)
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->when($subject, fn ($query) => $query->where('subject_type', $subject::class)->where('subject_id', $subject->getKey()))
            ->when($referenceCode, fn ($query) => $query->where('reference_code', $referenceCode))
            ->lockForUpdate()
            ->max('snapshot_version')) + 1;
    }

    private function brandingSnapshot(?School $school): array
    {
        return [
            'school_id' => $school?->id,
            'name' => $school?->name ?? config('sanfaani.platform_name'),
            'logo_url' => $school?->logoUrl(),
            'primary_color' => $school?->primary_color ?: '#047857',
            'secondary_color' => $school?->secondary_color ?: '#0f172a',
            'motto' => $school?->school_motto,
            'watermark' => $school?->name ?? config('sanfaani.platform_name'),
        ];
    }

    private function hashPayload(array $payload): string
    {
        return hash('sha256', json_encode($this->canonicalize($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->canonicalize($item), $value);
        }

        ksort($value);

        return array_map(fn ($item) => $this->canonicalize($item), $value);
    }

    private function verificationCode(): string
    {
        do {
            $code = 'PDF-'.strtoupper(Str::random(12));
        } while (PdfSnapshot::where('verification_code', $code)->exists());

        return $code;
    }
}
