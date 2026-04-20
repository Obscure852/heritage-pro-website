<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $palette = [
        '#2563EB',
        '#DC2626',
        '#059669',
        '#D97706',
        '#7C3AED',
        '#DB2777',
        '#0F766E',
        '#4B5563',
    ];

    public function up(): void
    {
        if (Schema::hasTable('houses') && !Schema::hasColumn('houses', 'color_code')) {
            Schema::table('houses', function (Blueprint $table): void {
                $table->string('color_code', 7)->default('#2563EB')->after('name');
            });
        }

        if (Schema::hasTable('final_houses') && !Schema::hasColumn('final_houses', 'color_code')) {
            Schema::table('final_houses', function (Blueprint $table): void {
                $table->string('color_code', 7)->nullable()->after('name');
            });
        }

        if (!Schema::hasTable('user_house')) {
            Schema::create('user_house', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('house_id');
                $table->unsignedBigInteger('term_id');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('house_id')->references('id')->on('houses')->onDelete('cascade');
                $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

                $table->unique(['user_id', 'term_id'], 'user_house_unique_user_term');
                $table->index(['house_id', 'term_id'], 'user_house_house_term_index');
                $table->index('term_id', 'user_house_term_index');
            });
        }

        $this->backfillHouseColors();
        $this->backfillFinalHouseColors();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_house');

        if (Schema::hasTable('final_houses') && Schema::hasColumn('final_houses', 'color_code')) {
            Schema::table('final_houses', function (Blueprint $table): void {
                $table->dropColumn('color_code');
            });
        }

        if (Schema::hasTable('houses') && Schema::hasColumn('houses', 'color_code')) {
            Schema::table('houses', function (Blueprint $table): void {
                $table->dropColumn('color_code');
            });
        }
    }

    private function backfillHouseColors(): void
    {
        if (!Schema::hasTable('houses') || !Schema::hasColumn('houses', 'color_code')) {
            return;
        }

        $houses = DB::table('houses')
            ->select('id')
            ->orderBy('term_id')
            ->orderBy('name')
            ->get();

        foreach ($houses as $index => $house) {
            DB::table('houses')
                ->where('id', $house->id)
                ->update([
                    'color_code' => $this->palette[$index % count($this->palette)],
                ]);
        }
    }

    private function backfillFinalHouseColors(): void
    {
        if (!Schema::hasTable('final_houses') || !Schema::hasColumn('final_houses', 'color_code')) {
            return;
        }

        $finalHouses = DB::table('final_houses')
            ->select('id', 'original_house_id')
            ->orderBy('graduation_term_id')
            ->orderBy('name')
            ->get();

        foreach ($finalHouses as $index => $house) {
            $colorCode = null;

            if ($house->original_house_id) {
                $colorCode = DB::table('houses')
                    ->where('id', $house->original_house_id)
                    ->value('color_code');
            }

            DB::table('final_houses')
                ->where('id', $house->id)
                ->update([
                    'color_code' => $colorCode ?: $this->palette[$index % count($this->palette)],
                ]);
        }
    }
};
