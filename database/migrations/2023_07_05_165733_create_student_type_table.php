<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{

    public function up(){
        Schema::create('student_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('description');
            $table->boolean('exempt')->default(false);
            $table->string('color', 7)->nullable();
            $table->timestamps();

            $table->index(['type', 'description']);
        });

        DB::table('student_types')->insert([
            [
                'description' => 'Orphan & Vulnerable Children',
                'type' => 'RAC',
                'exempt' => true,
                'color' => '#FF6B6B',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'description' => 'Rural Area Children',
                'type' => 'OVC',
                'exempt' => true,
                'color' => '#4ECDC4',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'description' => 'Special Education Needs',
                'type' => 'SEN',
                'exempt' => true,
                'color' => '#9B59B6',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }


    public function down(){
        Schema::dropIfExists('student_types');
    }
};
