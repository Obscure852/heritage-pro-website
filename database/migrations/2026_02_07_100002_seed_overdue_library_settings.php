<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $defaults = [
            [
                'key' => 'overdue_notification_schedule',
                'value' => json_encode(['days' => [1, 7, 14]]),
                'description' => 'Days after due date to send overdue reminders',
                'updated_at' => now(),
            ],
            [
                'key' => 'overdue_escalation',
                'value' => json_encode(['class_teacher_days' => 30, 'hod_days' => 45]),
                'description' => 'Days overdue before escalating to class teacher and HOD',
                'updated_at' => now(),
            ],
            [
                'key' => 'overdue_sms_enabled',
                'value' => json_encode(false),
                'description' => 'Enable SMS notifications for overdue items (default off to avoid surprise costs)',
                'updated_at' => now(),
            ],
        ];

        DB::table('library_settings')->insert($defaults);
    }

    public function down(): void {
        DB::table('library_settings')->whereIn('key', [
            'overdue_notification_schedule',
            'overdue_escalation',
            'overdue_sms_enabled',
        ])->delete();
    }
};
