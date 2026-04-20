<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('earning_bands')) {
            return;
        }

        Schema::create('earning_bands', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 50)->unique();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index('sort_order');
        });

        $timestamp = now();
        $bands = [
            'A1',
            'A2',
            'A3',
            'B5',
            'B4',
            'B3',
            'B2',
            'B1',
            'B5/3',
            'C4',
            'C3',
            'C2',
            'C1',
            'C4/3',
            'D4',
            'D3',
            'D2',
            'D1',
            'E2',
            'E1',
        ];

        DB::table('earning_bands')->insert(
            array_map(
                static fn (string $band, int $index): array => [
                    'name' => $band,
                    'sort_order' => $index + 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                $bands,
                array_keys($bands),
            ),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_bands');
    }
};
