<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Role::firstOrCreate(
            ['name' => 'Librarian'],
            ['description' => 'Full access to all library functions']
        );
    }

    public function down(): void {
        Role::where('name', 'Librarian')->delete();
    }
};
