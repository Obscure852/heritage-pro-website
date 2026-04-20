<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CreateUsersTable extends Migration{
    public function up(){
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_filter_id')->nullable();
            $table->unsignedBigInteger('reporting_to')->nullable();

            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('area_of_work')->default('Teaching')->nullable();
            $table->string('nationality')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('sms_signature')->nullable();
            $table->string('email_signature')->nullable();
            $table->string('phone')->nullable();
            $table->string('id_number')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->boolean('active')->default(true);
            $table->string('status')->default('Current');
            $table->string('username')->nullable();
            $table->year('year')->default(date('Y'));
            $table->string('last_updated_by')->default('Administrator')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_filter_id')->references('id')->on('user_filters')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('reporting_to')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->index('firstname');
            $table->index('lastname');
            $table->index('username');
            $table->index('phone');
            $table->index('reporting_to');
            $table->index(['department', 'position']);
            $table->index('status');
            $table->index('active');
            $table->index('user_filter_id');
            $table->index('last_updated_by');
        });

        try{
            DB::table('users')->insert([
                'firstname' => 'System',
                'lastname' => 'Admin',
                'email' => 'obscure852@gmail.com',
                'date_of_birth' => '1987-07-14',
                'gender' => 'M',
                'nationality' => 'Motswana',
                'phone' => '71869852',
                'id_number' => '346618812',
                'active' => true,
                'status' => 'Current',
                'position' => 'External Support',
                'department' => 'Administration',
                'area_of_work' => 'IT Support',
                'year' => date('Y'),
                'password' => Hash::make('#Ishallnotwant@2024'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
        }catch (\Exception $e) {
            Log::error("Failed to insert initial admin user: " . $e->getMessage());
        }
    }

    public function down(){
        Schema::dropIfExists('users');
    }
}
