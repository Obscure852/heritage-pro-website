<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get first admin user as creator
        $creator = User::first();

        if (!$creator) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $templates = [
            // Email Templates
            [
                'name' => 'Welcome Email',
                'type' => 'email',
                'subject' => 'Welcome to {{school_name}}',
                'body' => "Dear {{name}},\n\nWelcome to {{school_name}}! We're excited to have you join our community.\n\nIf you have any questions, please don't hesitate to reach out.\n\nBest regards,\n{{school_name}} Team",
                'description' => 'Welcome email for new sponsors/users',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Fee Reminder',
                'type' => 'email',
                'subject' => 'Fee Payment Reminder - {{student_name}}',
                'body' => "Dear {{parent_name}},\n\nThis is a friendly reminder that the school fees for {{student_name}} ({{amount}} BWP) are due on {{due_date}}.\n\nPlease ensure payment is made by the due date to avoid any inconvenience.\n\nThank you for your cooperation.\n\nBest regards,\n{{school_name}} Finance Department",
                'description' => 'Reminder email for school fee payments',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Meeting Invitation',
                'type' => 'email',
                'subject' => 'Invitation: {{meeting_title}}',
                'body' => "Dear {{name}},\n\nYou are invited to attend {{meeting_title}}.\n\nDate: {{meeting_date}}\nTime: {{meeting_time}}\nVenue: {{venue}}\n\nAgenda:\n{{agenda}}\n\nPlease confirm your attendance.\n\nBest regards,\n{{school_name}}",
                'description' => 'Meeting invitation email template',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Report Card Ready',
                'type' => 'email',
                'subject' => 'Report Card Available - {{student_name}}',
                'body' => "Dear {{parent_name}},\n\nWe are pleased to inform you that {{student_name}}'s report card for {{term}} is now available.\n\nYou can view and download the report card from the parent portal.\n\nIf you have any questions about your child's performance, please contact us.\n\nBest regards,\n{{school_name}} Academic Department",
                'description' => 'Notification when report cards are ready',
                'is_active' => true,
                'created_by' => $creator->id,
            ],

            // SMS Templates
            [
                'name' => 'Fee Payment Confirmation',
                'type' => 'sms',
                'subject' => null,
                'body' => "Dear {{parent_name}}, payment of {{amount}} BWP for {{student_name}} has been received. Thank you. - {{school_name}}",
                'description' => 'SMS confirmation for fee payment',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Absence Alert',
                'type' => 'sms',
                'subject' => null,
                'body' => "Dear {{parent_name}}, {{student_name}} was absent from school on {{date}}. Please contact us if this was not authorized. - {{school_name}}",
                'description' => 'Alert parents about student absence',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Event Reminder',
                'type' => 'sms',
                'subject' => null,
                'body' => "Reminder: {{event_name}} on {{date}} at {{time}}. Venue: {{venue}}. See you there! - {{school_name}}",
                'description' => 'Quick reminder for school events',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Emergency Alert',
                'type' => 'sms',
                'subject' => null,
                'body' => "URGENT: {{message}}. Please contact the school immediately at {{phone}}. - {{school_name}}",
                'description' => 'Emergency notification template',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Exam Timetable',
                'type' => 'sms',
                'subject' => null,
                'body' => "Dear {{parent_name}}, {{exam_name}} for {{student_name}} starts on {{start_date}}. Timetable: {{timetable_link}}. Good luck! - {{school_name}}",
                'description' => 'Exam schedule notification',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
            [
                'name' => 'Term Dates',
                'type' => 'sms',
                'subject' => null,
                'body' => "{{term}} starts on {{start_date}} and ends on {{end_date}}. Opening time: {{time}}. - {{school_name}}",
                'description' => 'Term start/end dates notification',
                'is_active' => true,
                'created_by' => $creator->id,
            ],
        ];

        foreach ($templates as $templateData) {
            // Extract variables from body and subject
            $variables = NotificationTemplate::extractVariables(
                $templateData['body'],
                $templateData['subject'] ?? null
            );

            $templateData['variables'] = $variables;

            NotificationTemplate::updateOrCreate(
                [
                    'name' => $templateData['name'],
                    'type' => $templateData['type'],
                ],
                $templateData
            );
        }

        $this->command->info('Notification templates seeded successfully!');
        $this->command->info('Total templates: ' . count($templates));
        $this->command->info('Email templates: 4');
        $this->command->info('SMS templates: 6');
    }
}
