<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Role::firstOrCreate(
            ['name' => 'Scheme Admin'],
            ['description' => 'Full access to create, edit, approve, publish, and distribute standard schemes of work']
        );

        Role::firstOrCreate(
            ['name' => 'Scheme View'],
            ['description' => 'Read-only access to view standard schemes of work and their distribution status']
        );
    }

    public function down(): void {
        Role::where('name', 'Scheme Admin')->delete();
        Role::where('name', 'Scheme View')->delete();
    }
};
