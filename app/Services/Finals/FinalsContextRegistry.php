<?php

namespace App\Services\Finals;

use App\Services\Finals\ImportProfiles\FinalsImportProfile;
use App\Services\Finals\ImportProfiles\JuniorJceImportProfile;
use App\Services\Finals\ImportProfiles\SeniorBgcseImportProfile;

class FinalsContextRegistry
{
    public function definition(string $context): FinalsContextDefinition
    {
        return $this->profile($context)->definition();
    }

    public function profile(string $context): FinalsImportProfile
    {
        return match (strtolower(trim($context))) {
            'senior' => new SeniorBgcseImportProfile(),
            default => new JuniorJceImportProfile(),
        };
    }
}
