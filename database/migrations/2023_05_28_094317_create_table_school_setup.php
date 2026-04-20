<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolSetup;

return new class extends Migration{

    public function up(){
        
        Schema::create('school_setup', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 30)->nullable()->unique();
            $table->string('ownership')->nullable();
            $table->string('school_name');
            $table->string('slogan')->nullable();
            $table->string('telephone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email_address')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('website')->nullable();
            $table->string('region')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('letterhead_path')->nullable();
            $table->string('type');
            $table->boolean('boarding')->nullable();
            $table->string('school_sms_signature')->nullable();
            $table->string('school_email_signature')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('school_name');
            $table->index('telephone');
            $table->index('email_address');
        });

        DB::table('school_setup')->insert([
            'school_name' => 'Pitikwe Junior Secondary School',
            'slogan' => 'Endure With Courage',
            'telephone' => '544 1118 ',
            'fax' => '533 0201',
            'email_address' => 'support@imagelife.co',
            'physical_address' => 'Lobatse, Botswana',
            'postal_address' => 'Private Bag 57,Lobatse',
            'website' => 'https://pitikwe.juniorschool.co',
            'region' => 'Southern',
            'logo_path' => 'storage/logos/example.png',
            'letterhead_path' => 'storage/letterheads/example.png',
            'type' => SchoolSetup::normalizeType(env('SCHOOL_TYPE', SchoolSetup::TYPE_JUNIOR)),
            'boarding' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(){
        Schema::dropIfExists('school_setup');
    }
};
