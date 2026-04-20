<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateLicensesTable extends Migration{
    public function up(){
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(true);
            $table->year('year');
            $table->timestamps();

            $table->index('name');
            $table->index('key');
        });

        // $now = Carbon::now();
        // DB::table('licenses')->insert([
        //     'name' => 'Developer License ' . $now->year,
        //     'key' => bin2hex(random_bytes(16)),
        //     'start_date' => $now->format('Y-m-d'),
        //     'end_date' => $now->addYear()->subDay()->format('Y-m-d'),
        //     'active' => true,
        //     'year' => $now->year,
        //     'created_at' => $now,
        //     'updated_at' => $now
        // ]);
    }

    public function down(){
        Schema::dropIfExists('licenses');
    }
}
