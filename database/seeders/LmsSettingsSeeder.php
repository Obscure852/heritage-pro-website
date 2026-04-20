<?php

namespace Database\Seeders;

use App\Models\SMSApiSetting;
use Illuminate\Database\Seeder;

class LmsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // File Upload Settings
            [
                'key' => 'lms_scorm_max_size_mb',
                'value' => '500',
                'type' => 'integer',
                'display_name' => 'SCORM Package Size (MB)',
                'description' => 'Maximum file size for SCORM packages in megabytes',
                'display_order' => 1,
            ],
            [
                'key' => 'lms_h5p_max_size_mb',
                'value' => '500',
                'type' => 'integer',
                'display_name' => 'H5P Package Size (MB)',
                'description' => 'Maximum file size for H5P packages in megabytes',
                'display_order' => 2,
            ],
            [
                'key' => 'lms_video_max_size_mb',
                'value' => '2048',
                'type' => 'integer',
                'display_name' => 'Video Upload Size (MB)',
                'description' => 'Maximum file size for video uploads in megabytes',
                'display_order' => 3,
            ],
            [
                'key' => 'lms_library_max_size_mb',
                'value' => '500',
                'type' => 'integer',
                'display_name' => 'Library File Size (MB)',
                'description' => 'Maximum file size for library uploads in megabytes',
                'display_order' => 4,
            ],

            // Assignment Settings
            [
                'key' => 'lms_assignment_max_size_mb',
                'value' => '100',
                'type' => 'integer',
                'display_name' => 'Assignment File Size (MB)',
                'description' => 'Maximum file size per assignment submission file',
                'display_order' => 10,
            ],
            [
                'key' => 'lms_assignment_max_files',
                'value' => '20',
                'type' => 'integer',
                'display_name' => 'Max Files per Assignment',
                'description' => 'Maximum number of files allowed per assignment submission',
                'display_order' => 11,
            ],
            [
                'key' => 'lms_assignment_default_types',
                'value' => 'pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip',
                'type' => 'string',
                'display_name' => 'Default Allowed File Types',
                'description' => 'Comma-separated list of allowed file extensions for assignments',
                'display_order' => 12,
            ],
            [
                'key' => 'lms_assignment_late_penalty_max',
                'value' => '100',
                'type' => 'integer',
                'display_name' => 'Max Late Penalty (%)',
                'description' => 'Maximum late submission penalty percentage',
                'display_order' => 13,
            ],
            [
                'key' => 'lms_assignment_max_points',
                'value' => '1000',
                'type' => 'integer',
                'display_name' => 'Max Assignment Points',
                'description' => 'Maximum points allowed for an assignment',
                'display_order' => 14,
            ],

            // Quiz Settings
            [
                'key' => 'lms_quiz_time_limit_max',
                'value' => '480',
                'type' => 'integer',
                'display_name' => 'Max Quiz Time Limit (min)',
                'description' => 'Maximum allowed quiz time limit in minutes',
                'display_order' => 20,
            ],
            [
                'key' => 'lms_quiz_points_max',
                'value' => '100',
                'type' => 'integer',
                'display_name' => 'Max Points per Question',
                'description' => 'Maximum points allowed per quiz question',
                'display_order' => 21,
            ],
            [
                'key' => 'lms_quiz_passing_score_default',
                'value' => '50',
                'type' => 'integer',
                'display_name' => 'Default Passing Score (%)',
                'description' => 'Default passing score percentage for quizzes',
                'display_order' => 22,
            ],

            // Course & Grading Settings
            [
                'key' => 'lms_course_passing_grade_default',
                'value' => '60',
                'type' => 'integer',
                'display_name' => 'Default Passing Grade (%)',
                'description' => 'Default passing grade percentage for courses',
                'display_order' => 30,
            ],
            [
                'key' => 'lms_gradebook_method_default',
                'value' => 'weighted',
                'type' => 'string',
                'display_name' => 'Default Grading Method',
                'description' => 'Default grading method (weighted, points, simple_average)',
                'display_order' => 31,
            ],
            [
                'key' => 'lms_gradebook_passing_grade_default',
                'value' => '50',
                'type' => 'integer',
                'display_name' => 'Default Gradebook Pass (%)',
                'description' => 'Default passing grade for gradebook calculations',
                'display_order' => 32,
            ],

            // Video Settings
            [
                'key' => 'lms_video_supported_formats',
                'value' => 'mp4,mov,avi,mkv,webm,wmv,flv,m4v',
                'type' => 'string',
                'display_name' => 'Supported Video Formats',
                'description' => 'Comma-separated list of supported video formats',
                'display_order' => 40,
            ],
            [
                'key' => 'lms_video_transcode_formats_default',
                'value' => '720p,480p,360p',
                'type' => 'string',
                'display_name' => 'Default Transcode Formats',
                'description' => 'Default video transcoding quality options',
                'display_order' => 41,
            ],
            [
                'key' => 'lms_video_completion_threshold',
                'value' => '90',
                'type' => 'integer',
                'display_name' => 'Video Completion Threshold (%)',
                'description' => 'Watch percentage required to mark video as complete',
                'display_order' => 42,
            ],

            // SCORM Settings
            [
                'key' => 'lms_scorm_supported_versions',
                'value' => '1.2,2004',
                'type' => 'string',
                'display_name' => 'Supported SCORM Versions',
                'description' => 'Comma-separated supported SCORM versions',
                'display_order' => 50,
            ],
            [
                'key' => 'lms_scorm_mastery_score_default',
                'value' => '70',
                'type' => 'integer',
                'display_name' => 'Default Mastery Score (%)',
                'description' => 'Default SCORM mastery score percentage',
                'display_order' => 51,
            ],

            // LTI Settings
            [
                'key' => 'lms_lti_version_default',
                'value' => '1.3',
                'type' => 'string',
                'display_name' => 'Default LTI Version',
                'description' => 'Default LTI version for new tools (1.1 or 1.3)',
                'display_order' => 60,
            ],
            [
                'key' => 'lms_lti_privacy_level_default',
                'value' => 'public',
                'type' => 'string',
                'display_name' => 'Default Privacy Level',
                'description' => 'Default LTI privacy level (public, name_only, anonymous)',
                'display_order' => 61,
            ],
            [
                'key' => 'lms_lti_score_max_default',
                'value' => '100',
                'type' => 'integer',
                'display_name' => 'Default Score Maximum',
                'description' => 'Default maximum score for LTI tools',
                'display_order' => 62,
            ],

            // Gamification Settings
            [
                'key' => 'lms_gamification_leaderboard_limit',
                'value' => '100',
                'type' => 'integer',
                'display_name' => 'Leaderboard Display Limit',
                'description' => 'Maximum number of entries shown on leaderboard',
                'display_order' => 70,
            ],
            [
                'key' => 'lms_gamification_activity_limit',
                'value' => '50',
                'type' => 'integer',
                'display_name' => 'Activity Display Limit',
                'description' => 'Maximum number of recent activities to display',
                'display_order' => 71,
            ],
            [
                'key' => 'lms_gamification_points_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'display_name' => 'Enable Points System',
                'description' => 'Enable or disable the gamification points system',
                'display_order' => 72,
            ],
            [
                'key' => 'lms_gamification_badges_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'display_name' => 'Enable Badges',
                'description' => 'Enable or disable badge awards',
                'display_order' => 73,
            ],
        ];

        foreach ($settings as $setting) {
            SMSApiSetting::updateOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'category' => 'lms',
                    'is_editable' => true,
                ])
            );
        }

        $this->command->info('LMS settings seeded successfully!');
    }
}
