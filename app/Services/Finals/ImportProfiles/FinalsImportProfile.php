<?php

namespace App\Services\Finals\ImportProfiles;

use App\Services\Finals\FinalsContextDefinition;

interface FinalsImportProfile
{
    public function definition(): FinalsContextDefinition;

    /**
     * @return object
     */
    public function parserService();

    /**
     * @return array<string, string>
     */
    public function subjectCodeMap(): array;

    /**
     * @return array<string, string>
     */
    public function defaultSubjectNameMap(): array;
}
