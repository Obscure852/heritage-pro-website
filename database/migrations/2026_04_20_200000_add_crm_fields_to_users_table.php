<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $hasActive = Schema::hasColumn('users', 'active');
        $hasRole = Schema::hasColumn('users', 'role');

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true)->after('password');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('rep')->after('active');
            }
        });

        if (! $hasActive || ! $hasRole) {
            DB::table('users')->update([
                'active' => true,
                'role' => 'admin',
            ]);
        } else {
            DB::table('users')->whereNull('active')->update(['active' => true]);
            DB::table('users')->whereNull('role')->update(['role' => 'admin']);
            DB::table('users')->where('role', '')->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('users', 'active')) {
                $columns[] = 'active';
            }

            if (Schema::hasColumn('users', 'role')) {
                $columns[] = 'role';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
