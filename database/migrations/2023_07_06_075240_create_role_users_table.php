<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
Use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

return new class extends Migration{
 
    public function up(){
        Schema::create('role_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();

            $table->index('role_id');
            $table->index('User_id');
        });

        try{
            $adminRoleId = DB::table('roles')->where('name', 'Administrator')->first()->id;
            $systemSetupRoleId = DB::table('roles')->where('name', 'System Setup')->first()->id;
            $systemDataImportRoleId = DB::table('roles')->where('name', 'Data Importing')->first()->id;
            $adminUserId = DB::table('users')->where('email', 'obscure852@gmail.com')->first()->id;

            $user = User::findOrFail($adminUserId);
            $user->roles()->attach([$adminRoleId, $systemSetupRoleId,$systemDataImportRoleId]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Role or user not found: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::error("Failed to assign role to user: " . $e->getMessage());
        }

    }

    public function down(){
        Schema::dropIfExists('role_users');
    }
};
