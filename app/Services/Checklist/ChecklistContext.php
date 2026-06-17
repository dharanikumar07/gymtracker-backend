<?php

namespace App\Services\Checklist;

use App\Models\User;

class ChecklistContext
{
    public array $steps = [];

    public function __construct(
        public readonly User $user,
    ) {}

    public function addStep(string $key, string $label, bool $enabled, string $url, string $description = ''): void
    {
        $this->steps[] = [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'enabled' => $enabled,
            'url' => $url,
        ];
    }
}
