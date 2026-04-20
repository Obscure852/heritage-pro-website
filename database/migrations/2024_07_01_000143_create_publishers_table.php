<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{
    
    public function up(){
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();

            $table->index('name');
        });

        $publishers = [
            'Pearson',
            'Macmillan',
            'Botsalano Press',
            'Collegium Educational Publisher',
            'Pula Press',
            'Mmegi Publishing House',
            'Breakthrough Publishers',
            'Sunrise Educational Publishing',
            'Diamond',
            'Oxford University Press',
            'Pentagon Publishers',
            'Medi Publishing',
            'Heinemann',
            'Longman',
            'Maskew Miller Longman',
            'Cambridge University Press',
        ];

        foreach ($publishers as $publisher) {
            DB::table('publishers')->insert(['name' => $publisher]);
        }
    }

    public function down(){
        Schema::dropIfExists('publishers');
    }
};
