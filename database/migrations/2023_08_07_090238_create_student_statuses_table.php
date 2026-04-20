<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{

    public function up(){
        Schema::create('student_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        DB::table('student_statuses')->insert([
            ['name' => 'Current' ],
            ['name' => 'Deleted' ],
            ['name' => 'Past Student' ],
            ['name' => 'Transfered' ],
            ['name' => 'Suspended' ],
            ['name' => 'Illness' ],
        ]);
    }

    public function down(){
        Schema::dropIfExists('student_statuses');
    }
};
