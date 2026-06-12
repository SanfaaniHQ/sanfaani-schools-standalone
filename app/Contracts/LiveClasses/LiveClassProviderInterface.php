<?php

namespace App\Contracts\LiveClasses;

interface LiveClassProviderInterface
{
    public function key(): string;

    public function label(): string;

    public function description(): string;

    /**
     * @return array<string, bool>
     */
    public function capabilities(): array;

    public function requiresCredentials(): bool;

    public function supportsManualLink(): bool;

    public function supportsAutoCreate(): bool;

    public function validateManualMeetingUrl(?string $url): bool;

    public function validateRecordingUrl(?string $url): bool;

    /**
     * @return list<string>
     */
    public function boundaryNotes(): array;
}
