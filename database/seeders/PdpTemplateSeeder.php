<?php

namespace Database\Seeders;

use App\Services\Pdp\PdpTemplateService;
use Illuminate\Database\Seeder;

class PdpTemplateSeeder extends Seeder
{
    public function run(): void
    {
        app(PdpTemplateService::class)->seedDefaults();
    }
}
