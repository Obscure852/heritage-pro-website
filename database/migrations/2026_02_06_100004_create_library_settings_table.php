<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('key');
        });

        $defaults = [
            [
                'key' => 'loan_period',
                'value' => json_encode(['student' => 14, 'staff' => 30]),
                'description' => 'Loan period in days per borrower type',
                'updated_at' => now(),
            ],
            [
                'key' => 'max_books',
                'value' => json_encode(['student' => 3, 'staff' => 5]),
                'description' => 'Maximum books per borrower type',
                'updated_at' => now(),
            ],
            [
                'key' => 'fine_rate_per_day',
                'value' => json_encode(['student' => 1.00, 'staff' => 2.00]),
                'description' => 'Fine rate per overdue day (BWP) per borrower type',
                'updated_at' => now(),
            ],
            [
                'key' => 'max_renewals',
                'value' => json_encode(['student' => 1, 'staff' => 2]),
                'description' => 'Maximum renewals per borrower type',
                'updated_at' => now(),
            ],
            [
                'key' => 'fine_threshold',
                'value' => json_encode(['amount' => 50.00]),
                'description' => 'Outstanding fine amount that blocks borrowing (BWP)',
                'updated_at' => now(),
            ],
            [
                'key' => 'lost_book_period',
                'value' => json_encode(['student' => 60, 'staff' => 60]),
                'description' => 'Days overdue before declaring book lost per borrower type',
                'updated_at' => now(),
            ],
        ];
        DB::table('library_settings')->insert($defaults);
    }

    public function down(): void {
        Schema::dropIfExists('library_settings');
    }
};
