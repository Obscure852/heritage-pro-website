<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Seed currency setting
        DB::table('library_settings')->updateOrInsert(
            ['key' => 'library_currency'],
            [
                'value' => json_encode(['code' => 'BWP']),
                'description' => 'Currency code used for fines and pricing',
                'updated_at' => now(),
            ]
        );

        // Seed default catalog locations
        DB::table('library_settings')->updateOrInsert(
            ['key' => 'catalog_locations'],
            [
                'value' => json_encode([
                    'Main Library',
                    'Reference Section',
                    'Fiction Corner',
                    'Study Room',
                    'Teacher Resource Room',
                ]),
                'description' => 'Available shelf/area locations for organizing items',
                'updated_at' => now(),
            ]
        );

        // Seed default catalog categories
        DB::table('library_settings')->updateOrInsert(
            ['key' => 'catalog_categories'],
            [
                'value' => json_encode([
                    'Fiction',
                    'Non-Fiction',
                    'Reference',
                    'Textbook',
                    'Science',
                    'Mathematics',
                    'History',
                    'Geography',
                    'Literature',
                    'Biography',
                ]),
                'description' => 'Item categories used in the catalog',
                'updated_at' => now(),
            ]
        );

        // Seed default reading levels
        DB::table('library_settings')->updateOrInsert(
            ['key' => 'catalog_reading_levels'],
            [
                'value' => json_encode([
                    'Beginner',
                    'Intermediate',
                    'Advanced',
                ]),
                'description' => 'Reading proficiency levels for catalog items',
                'updated_at' => now(),
            ]
        );

        // Seed default item types
        DB::table('library_settings')->updateOrInsert(
            ['key' => 'catalog_item_types'],
            [
                'value' => json_encode([
                    ['name' => 'Book'],
                    ['name' => 'Magazine'],
                    ['name' => 'DVD'],
                    ['name' => 'Map'],
                ]),
                'description' => 'Available item types with optional per-type borrowing rules',
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('library_settings')->where('key', 'library_currency')->delete();
        // Don't delete catalog options on rollback — they may have been customized
    }
};
