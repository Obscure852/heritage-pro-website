<?php

namespace App\Services\Pdp;

class PdpTemplateBlueprints
{
    public static function defaultPerformanceObjectiveCategoryOptions(): array
    {
        return [
            ['value' => 'Attendance', 'label' => 'Attendance'],
            ['value' => 'Academic Performance', 'label' => 'Academic Performance'],
            ['value' => 'Stakeholder Involvement', 'label' => 'Stakeholder Involvement'],
        ];
    }

    public static function sharedRowSectionConfig(?string $key): ?array
    {
        if ($key === null) {
            return null;
        }

        return self::sharedRowSectionConfigs()[$key] ?? null;
    }

    public static function catalog(): array
    {
        return [
            'blank_bounded' => [
                'label' => 'Blank Bounded Template',
                'description' => 'Start from the bounded Staff PDP structure with no shared objectives defined yet.',
            ],
            'school_half_yearly' => [
                'label' => 'School Half-Yearly',
                'description' => 'Seeded school-adapted template with template-owned objectives and school-wide rollout support.',
            ],
            'official_dpsm' => [
                'label' => 'Official DPSM',
                'description' => 'Seeded official template with template-owned objectives and DPSM summary flow.',
            ],
        ];
    }

    public static function definitionByKey(string $key): array
    {
        return match ($key) {
            'blank_bounded' => self::blankBounded(),
            'school_half_yearly' => self::schoolHalfYearly(),
            'official_dpsm' => self::officialDpsm(),
            default => throw new \InvalidArgumentException("Unknown PDP blueprint [{$key}]."),
        };
    }

    public static function blankBounded(): array
    {
        $definition = self::schoolHalfYearly();

        $definition['template'] = [
            'template_family_key' => 'staff_pdp_custom',
            'version' => 1,
            'code' => 'staff-pdp-custom-v1',
            'name' => 'Staff PDP - Custom Builder v1',
            'source_reference' => null,
            'description' => 'Blank bounded Staff PDP template for defining your own shared objectives, cadence, scoring, and approval chain.',
            'settings_json' => [
                'baseline' => 'blank_bounded',
                'pdf' => [
                    'title' => 'Staff Performance Development Plan',
                    'show_logo' => true,
                ],
            ],
        ];

        return self::normalizeSharedRowSections($definition, true);
    }

    public static function schoolHalfYearly(): array
    {
        $definition = [
            'template' => [
                'template_family_key' => 'staff_pdp_school',
                'version' => 4,
                'code' => 'staff-pdp-school-v4',
                'name' => 'Staff PDP - School Half-Yearly v4',
                'source_reference' => 'PART B PERFORMANCE OBJECTIVES copy.pdf',
                'description' => 'Default school-adapted half-yearly PDP template with template-owned performance objectives, bounded evaluation fields, and direct user-field mappings for payroll, appointment date, and earning band.',
                'settings_json' => [
                    'baseline' => 'school_half_yearly',
                    'pdf' => [
                        'title' => 'Staff Performance Development Plan',
                        'show_logo' => true,
                    ],
                ],
            ],
            'sections' => [
                [
                    'key' => 'employee_information',
                    'label' => 'Part A: Employee Information',
                    'section_type' => 'profile_summary',
                    'sequence' => 1,
                    'fields' => [
                        [
                            'key' => 'employee_name',
                            'label' => 'Name of Employee',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'full_name',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'payroll_no',
                            'label' => 'Personal Payroll Number',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'personal_payroll_number',
                            'required' => false,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'dpsm_file_no',
                            'label' => 'DPSM Personal File No',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'dpsm_personal_file_number',
                            'required' => false,
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'plan_period_start',
                            'label' => 'Plan Period From',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'plan_period_start',
                            'required' => true,
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'plan_period_end',
                            'label' => 'Plan Period To',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'plan_period_end',
                            'required' => true,
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'ministry_department',
                            'label' => 'Ministry / Department',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'pdp.general.part_a_ministry_department',
                            'required' => false,
                            'sort_order' => 6,
                        ],
                        [
                            'key' => 'school_name',
                            'label' => 'Division / Unit',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'school_setup.school_name',
                            'required' => false,
                            'sort_order' => 7,
                        ],
                        [
                            'key' => 'position_title',
                            'label' => 'Position Title',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'position',
                            'required' => true,
                            'sort_order' => 8,
                        ],
                        [
                            'key' => 'grade',
                            'label' => 'Grade (Earning Band)',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'earning_band',
                            'required' => false,
                            'sort_order' => 9,
                        ],
                        [
                            'key' => 'date_of_appointment',
                            'label' => 'Date of Appointment',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'date_of_appointment',
                            'required' => false,
                            'sort_order' => 10,
                        ],
                        [
                            'key' => 'duty_station',
                            'label' => 'Duty Station',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'school_setup.school_name',
                            'required' => false,
                            'sort_order' => 11,
                        ],
                        [
                            'key' => 'supervisor_name',
                            'label' => 'Supervisor Name',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'supervisor.full_name',
                            'required' => false,
                            'sort_order' => 12,
                        ],
                        [
                            'key' => 'supervisor_position',
                            'label' => 'Supervisor Position',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'supervisor.position',
                            'required' => false,
                            'sort_order' => 13,
                        ],
                        [
                            'key' => 'supervisor_grade',
                            'label' => 'Supervisor Grade',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'supervisor.earning_band',
                            'required' => false,
                            'sort_order' => 14,
                        ],
                        [
                            'key' => 'supervisor_duty_station',
                            'label' => 'Supervisor Duty Station',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'school_setup.school_name',
                            'required' => false,
                            'sort_order' => 15,
                        ],
                    ],
                ],
                [
                    'key' => 'performance_objectives',
                    'label' => 'Part B: Performance Objectives',
                    'section_type' => 'repeatable_objectives',
                    'sequence' => 2,
                    'is_repeatable' => true,
                    'min_items' => 1,
                    'layout_config_json' => [
                        'display' => 'accordion',
                        'row_source' => 'template_section_rows',
                        'template_managed_field_keys' => ['objective_category', 'objective', 'output', 'measure', 'target'],
                        'template_parent_field_keys' => ['objective_category', 'objective'],
                        'template_child_field_keys' => ['output', 'measure', 'target'],
                        'plan_evaluation_field_keys' => ['score_out_of_10', 'supervisee_comment', 'supervisor_comment'],
                        'allow_custom_entries' => false,
                    ],
                    'fields' => [
                        [
                            'key' => 'objective_category',
                            'label' => 'Category',
                            'field_type' => 'select',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'options_json' => self::defaultPerformanceObjectiveCategoryOptions(),
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'objective',
                            'label' => 'Performance Objective',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'output',
                            'label' => 'Output',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'measure',
                            'label' => 'Measure',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'target',
                            'label' => 'Target',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'score_out_of_10',
                            'label' => 'Score Out of 10',
                            'field_type' => 'number',
                            'data_type' => 'decimal',
                            'input_mode' => 'manual_entry',
                            'rating_scheme_key' => 'performance_percentage',
                            'sort_order' => 6,
                        ],
                        [
                            'key' => 'supervisee_comment',
                            'label' => 'Supervisee Comment',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 7,
                        ],
                        [
                            'key' => 'supervisor_comment',
                            'label' => 'Supervisor Comment',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 8,
                        ],
                    ],
                ],
                [
                    'key' => 'coaching',
                    'label' => 'Part C: Coaching / Development Objectives',
                    'section_type' => 'repeatable_development',
                    'sequence' => 3,
                    'is_repeatable' => true,
                    'fields' => [
                        [
                            'key' => 'development_objective',
                            'label' => 'Development Objective',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'expected_result',
                            'label' => 'Expected Result',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'supervisor_follow_up',
                            'label' => 'Supervisor Follow Up',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 3,
                        ],
                    ],
                ],
                [
                    'key' => 'behavioural_attributes',
                    'label' => 'Part D: Behavioural Attributes',
                    'section_type' => 'repeatable_attributes',
                    'sequence' => 4,
                    'is_repeatable' => true,
                    'min_items' => 6,
                    'layout_config_json' => [
                        'seed_rows' => [
                            ['attribute_name' => 'Time Management', 'description' => 'Punctuality, deadlines, scheduling', 'applicable' => true],
                            ['attribute_name' => 'Creativity and Innovation', 'description' => 'Fresh ideas and problem-solving', 'applicable' => true],
                            ['attribute_name' => 'Teamwork', 'description' => 'Works with and supports others', 'applicable' => true],
                            ['attribute_name' => 'Work Ethic', 'description' => 'Integrity, appearance, Botho', 'applicable' => true],
                            ['attribute_name' => 'Customer Focus', 'description' => 'Addresses concerns and follows up', 'applicable' => true],
                            ['attribute_name' => 'Effective Communication', 'description' => 'Shares information and listens well', 'applicable' => true],
                            ['attribute_name' => 'Supervisory Skills', 'description' => 'Delegation, motivation, staff support', 'applicable' => false],
                            ['attribute_name' => 'Managerial Performance', 'description' => 'Planning, organizing, resource direction', 'applicable' => false],
                        ],
                    ],
                    'fields' => [
                        [
                            'key' => 'attribute_name',
                            'label' => 'Attribute Name',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'applicable',
                            'label' => 'Applicable',
                            'field_type' => 'select',
                            'data_type' => 'boolean',
                            'input_mode' => 'manual_entry',
                            'default_value_json' => true,
                            'options_json' => [
                                ['value' => true, 'label' => 'Yes'],
                                ['value' => false, 'label' => 'No'],
                            ],
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'mid_year_rating',
                            'label' => 'Mid-Year Rating',
                            'field_type' => 'radio_scale',
                            'data_type' => 'integer',
                            'input_mode' => 'manual_entry',
                            'period_scope' => 'mid_year',
                            'rating_scheme_key' => 'behaviour_intensity',
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'year_end_rating',
                            'label' => 'Year-End Rating',
                            'field_type' => 'radio_scale',
                            'data_type' => 'integer',
                            'input_mode' => 'manual_entry',
                            'period_scope' => 'year_end',
                            'rating_scheme_key' => 'behaviour_intensity',
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'comment',
                            'label' => 'Comment',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 6,
                        ],
                    ],
                ],
                [
                    'key' => 'personal_development_goals',
                    'label' => 'Part E: Personal Development Goals',
                    'section_type' => 'repeatable_development',
                    'sequence' => 5,
                    'is_repeatable' => true,
                    'fields' => [
                        [
                            'key' => 'performance_gap',
                            'label' => 'Performance Gap',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'agreed_action',
                            'label' => 'Agreed Action',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'time_frame',
                            'label' => 'Time Frame',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'result',
                            'label' => 'Result',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 4,
                        ],
                    ],
                ],
                [
                    'key' => 'review_summary',
                    'label' => 'Part F: Half-Yearly Review Rating Summary',
                    'section_type' => 'review_summary',
                    'sequence' => 6,
                    'fields' => [
                        [
                            'key' => 'mid_year_performance',
                            'label' => 'Mid-Year Performance',
                            'field_type' => 'computed_value',
                            'data_type' => 'decimal',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.mid_year_performance',
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'mid_year_attributes',
                            'label' => 'Mid-Year Attributes',
                            'field_type' => 'computed_value',
                            'data_type' => 'decimal',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.mid_year_attributes',
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'mid_year_total',
                            'label' => 'Mid-Year Total',
                            'field_type' => 'computed_value',
                            'data_type' => 'decimal',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.mid_year_total',
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'year_end_total',
                            'label' => 'Year-End Total',
                            'field_type' => 'computed_value',
                            'data_type' => 'decimal',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.year_end_total',
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'final_rating_band',
                            'label' => 'Final Rating Band',
                            'field_type' => 'computed_value',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.final_rating_band',
                            'sort_order' => 5,
                        ],
                    ],
                ],
            ],
            'periods' => [
                [
                    'key' => 'mid_year',
                    'label' => 'Mid-Year Review',
                    'sequence' => 1,
                    'window_type' => 'configured_dates',
                    'summary_label' => 'Mid-Year Remarks',
                ],
                [
                    'key' => 'year_end',
                    'label' => 'Year-End Review',
                    'sequence' => 2,
                    'window_type' => 'configured_dates',
                    'summary_label' => 'Year-End Remarks',
                ],
            ],
            'rating_schemes' => [
                [
                    'key' => 'performance_percentage',
                    'label' => 'Objective Score (0-10)',
                    'input_type' => 'intensity_scale',
                    'weight' => 0.80,
                    'rounding_rule' => 'round_2',
                    'scale_config_json' => ['min' => 0, 'max' => 10],
                    'conversion_config_json' => ['type' => 'rating_to_percentage'],
                    'formula_config_json' => ['type' => 'average_then_weight'],
                ],
                [
                    'key' => 'behaviour_intensity',
                    'label' => 'Behavioural Intensity',
                    'input_type' => 'intensity_scale',
                    'weight' => 0.20,
                    'rounding_rule' => 'round_2',
                    'scale_config_json' => [
                        'min' => 1,
                        'max' => 5,
                        'labels' => [
                            1 => 'Unsatisfactory',
                            2 => 'Fair',
                            3 => 'Good',
                            4 => 'Very Good',
                            5 => 'Outstanding',
                        ],
                    ],
                    'conversion_config_json' => ['type' => 'rating_to_percentage'],
                ],
                [
                    'key' => 'final_band',
                    'label' => 'Final Rating Band',
                    'input_type' => 'band_lookup',
                    'band_config_json' => [
                        ['min' => 95, 'max' => 100, 'label' => 'Outstanding'],
                        ['min' => 80, 'max' => 94.99, 'label' => 'Very Good'],
                        ['min' => 65, 'max' => 79.99, 'label' => 'Satisfactory/Good'],
                        ['min' => 50, 'max' => 64.99, 'label' => 'Fair'],
                        ['min' => 0, 'max' => 49.99, 'label' => 'Unsatisfactory'],
                    ],
                ],
            ],
            'approval_steps' => [
                [
                    'key' => 'employee_signoff',
                    'label' => 'Employee Signature',
                    'sequence' => 1,
                    'role_type' => 'employee',
                    'required' => true,
                ],
                [
                    'key' => 'supervisor_signoff',
                    'label' => 'Supervisor Signature',
                    'sequence' => 2,
                    'role_type' => 'reporting_officer',
                    'required' => true,
                ],
                [
                    'key' => 'authorized_official_signoff',
                    'label' => 'Authorized Official Signature',
                    'sequence' => 3,
                    'role_type' => 'authorized_official',
                    'required' => true,
                ],
            ],
        ];

        return self::normalizeSharedRowSections($definition);
    }

    public static function officialDpsm(): array
    {
        $definition = [
            'template' => [
                'template_family_key' => 'staff_pdp_dpsm',
                'version' => 4,
                'code' => 'staff-pdp-dpsm-v4',
                'name' => 'Staff PDP - Official DPSM Form 6 v4',
                'source_reference' => 'PDP.docx',
                'description' => 'Official DPSM Form 6 template with template-owned performance objectives, bounded evaluation fields, and direct user-field mappings for payroll, appointment date, and earning band.',
                'settings_json' => [
                    'baseline' => 'official_dpsm',
                    'pdf' => [
                        'title' => 'Performance and Development Plan and Review Document',
                        'show_logo' => true,
                    ],
                ],
            ],
            'sections' => [
                [
                    'key' => 'employee_information',
                    'label' => 'Part A: Employee Information',
                    'section_type' => 'profile_summary',
                    'sequence' => 1,
                    'fields' => [
                        [
                            'key' => 'employee_name',
                            'label' => 'Name of Employee',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'full_name',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'payroll_no',
                            'label' => 'Personal Payroll Number',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'personal_payroll_number',
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'dpsm_file_no',
                            'label' => 'DPSM Personal File No',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'dpsm_personal_file_number',
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'plan_period_start',
                            'label' => 'Plan Period From',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'plan_period_start',
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'plan_period_end',
                            'label' => 'Plan Period To',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'plan_period_end',
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'ministry_department',
                            'label' => 'Ministry / Department',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'pdp.general.part_a_ministry_department',
                            'sort_order' => 6,
                        ],
                        [
                            'key' => 'position_title',
                            'label' => 'Position Title',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'position',
                            'sort_order' => 7,
                        ],
                        [
                            'key' => 'grade',
                            'label' => 'Grade (Earning Band)',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'earning_band',
                            'sort_order' => 8,
                        ],
                        [
                            'key' => 'date_of_appointment',
                            'label' => 'Date of Appointment',
                            'field_type' => 'date',
                            'data_type' => 'date',
                            'input_mode' => 'mapped_user_field',
                            'mapping_source' => 'user',
                            'mapping_key' => 'date_of_appointment',
                            'sort_order' => 9,
                        ],
                        [
                            'key' => 'school_name',
                            'label' => 'Division / Unit',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'school_setup.school_name',
                            'sort_order' => 10,
                        ],
                        [
                            'key' => 'duty_station',
                            'label' => 'Duty Station',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'school_setup.school_name',
                            'sort_order' => 11,
                        ],
                        [
                            'key' => 'supervisor_name',
                            'label' => 'Supervisor Name',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'supervisor.full_name',
                            'sort_order' => 12,
                        ],
                        [
                            'key' => 'supervisor_position',
                            'label' => 'Supervisor Position',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'supervisor.position',
                            'sort_order' => 13,
                        ],
                        [
                            'key' => 'supervisor_grade',
                            'label' => 'Supervisor Grade',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'computed',
                            'mapping_source' => 'plan',
                            'mapping_key' => 'supervisor.earning_band',
                            'sort_order' => 14,
                        ],
                        [
                            'key' => 'supervisor_duty_station',
                            'label' => 'Supervisor Duty Station',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'mapped_setting',
                            'mapping_source' => 'settings',
                            'mapping_key' => 'school_setup.school_name',
                            'sort_order' => 15,
                        ],
                    ],
                ],
                [
                    'key' => 'performance_objectives',
                    'label' => 'Part B: Performance Objectives',
                    'section_type' => 'repeatable_objectives',
                    'sequence' => 2,
                    'is_repeatable' => true,
                    'min_items' => 1,
                    'layout_config_json' => [
                        'row_source' => 'template_section_rows',
                        'template_managed_field_keys' => ['objective_category', 'objective', 'output', 'measure', 'target'],
                        'template_parent_field_keys' => ['objective_category', 'objective'],
                        'template_child_field_keys' => ['output', 'measure', 'target'],
                        'plan_evaluation_field_keys' => ['score_out_of_10', 'supervisee_comment', 'supervisor_comment'],
                        'allow_custom_entries' => false,
                    ],
                    'fields' => [
                        [
                            'key' => 'objective_category',
                            'label' => 'Category',
                            'field_type' => 'select',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'options_json' => self::defaultPerformanceObjectiveCategoryOptions(),
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'objective',
                            'label' => 'Objective',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'output',
                            'label' => 'Output',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 3,
                        ],
                        [
                            'key' => 'measure',
                            'label' => 'Measure',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 4,
                        ],
                        [
                            'key' => 'target',
                            'label' => 'Target',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 5,
                        ],
                        [
                            'key' => 'score_out_of_10',
                            'label' => 'Score Out of 10',
                            'field_type' => 'number',
                            'data_type' => 'decimal',
                            'input_mode' => 'manual_entry',
                            'rating_scheme_key' => 'performance_percentage',
                            'sort_order' => 6,
                        ],
                        [
                            'key' => 'supervisee_comment',
                            'label' => 'Supervisee Comment',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 7,
                        ],
                        [
                            'key' => 'supervisor_comment',
                            'label' => 'Supervisor Comment',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 8,
                        ],
                    ],
                ],
                [
                    'key' => 'development_objectives',
                    'label' => 'Part C: Development Objectives',
                    'section_type' => 'repeatable_development',
                    'sequence' => 3,
                    'is_repeatable' => true,
                    'fields' => [
                        [
                            'key' => 'development_objective',
                            'label' => 'Individual Development Objective',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'expected_result',
                            'label' => 'Expected Result',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'supervisor_comment',
                            'label' => 'Follow Up / Comments by Supervisor',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 3,
                        ],
                    ],
                ],
                [
                    'key' => 'personal_attributes',
                    'label' => 'Part D: Assessment for Personal Attributes',
                    'section_type' => 'repeatable_attributes',
                    'sequence' => 4,
                    'is_repeatable' => true,
                    'layout_config_json' => [
                        'seed_rows' => [
                            ['attribute_name' => 'Time Management', 'description' => 'Time keeping and deadlines'],
                            ['attribute_name' => 'Knowledge of the Work', 'description' => 'Knowledge of purpose, processes, and practice'],
                            ['attribute_name' => 'Output: Accuracy, Reliability & Speed', 'description' => 'Quality and reliability of output'],
                            ['attribute_name' => 'Customer Care', 'description' => 'Value-based customer-focused initiatives'],
                            ['attribute_name' => 'Teamwork', 'description' => 'Participation and support for team efforts'],
                            ['attribute_name' => 'Initiative', 'description' => 'Initiatives resulting in accomplishment'],
                            ['attribute_name' => 'Supervisory Abilities', 'description' => 'Guidance for achieving results'],
                            ['attribute_name' => 'Managerial Performance', 'description' => 'Planning, organizing, and directing resources'],
                        ],
                    ],
                    'fields' => [
                        [
                            'key' => 'attribute_name',
                            'label' => 'Attribute Name',
                            'field_type' => 'text',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'required' => true,
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'description',
                            'label' => 'Description',
                            'field_type' => 'textarea',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'rating',
                            'label' => 'Rating',
                            'field_type' => 'radio_scale',
                            'data_type' => 'integer',
                            'input_mode' => 'manual_entry',
                            'rating_scheme_key' => 'personal_attribute_band',
                            'sort_order' => 3,
                        ],
                    ],
                ],
                [
                    'key' => 'quarterly_summary',
                    'label' => 'Quarterly Review Rating Summary',
                    'section_type' => 'review_summary',
                    'sequence' => 5,
                    'fields' => [
                        [
                            'key' => 'quarterly_total',
                            'label' => 'Quarterly Total',
                            'field_type' => 'computed_value',
                            'data_type' => 'decimal',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.quarterly_total',
                            'sort_order' => 1,
                        ],
                    ],
                ],
                [
                    'key' => 'final_summary',
                    'label' => 'Part E: Summary and Recommendation(s)',
                    'section_type' => 'comments_block',
                    'sequence' => 6,
                    'fields' => [
                        [
                            'key' => 'final_rating',
                            'label' => 'Final Rating for the Year',
                            'field_type' => 'computed_value',
                            'data_type' => 'decimal',
                            'input_mode' => 'computed',
                            'mapping_source' => 'computed',
                            'mapping_key' => 'summary.final_rating',
                            'sort_order' => 1,
                        ],
                        [
                            'key' => 'reward_recommendation',
                            'label' => 'Reward Recommendation',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 2,
                        ],
                        [
                            'key' => 'development_required',
                            'label' => 'Development Required',
                            'field_type' => 'comment',
                            'data_type' => 'string',
                            'input_mode' => 'manual_entry',
                            'sort_order' => 3,
                        ],
                    ],
                ],
            ],
            'periods' => [
                ['key' => 'quarter_1', 'label' => 'Quarter 1', 'sequence' => 1, 'window_type' => 'configured_dates', 'summary_label' => 'Quarter 1'],
                ['key' => 'quarter_2', 'label' => 'Quarter 2', 'sequence' => 2, 'window_type' => 'configured_dates', 'summary_label' => 'Quarter 2'],
                ['key' => 'quarter_3', 'label' => 'Quarter 3', 'sequence' => 3, 'window_type' => 'configured_dates', 'summary_label' => 'Quarter 3'],
                ['key' => 'quarter_4', 'label' => 'Quarter 4', 'sequence' => 4, 'window_type' => 'configured_dates', 'summary_label' => 'Quarter 4'],
            ],
            'rating_schemes' => [
                [
                    'key' => 'performance_percentage',
                    'label' => 'Objective Score (0-10)',
                    'input_type' => 'intensity_scale',
                    'weight' => 0.80,
                    'rounding_rule' => 'round_2',
                    'scale_config_json' => ['min' => 0, 'max' => 10],
                    'conversion_config_json' => ['type' => 'rating_to_percentage'],
                ],
                [
                    'key' => 'personal_attribute_band',
                    'label' => 'Personal Attribute Band',
                    'input_type' => 'band_scale',
                    'weight' => 0.20,
                    'band_config_json' => [
                        ['value' => 5, 'label' => 'Outstanding'],
                        ['value' => 4, 'label' => 'Very Good'],
                        ['value' => 3, 'label' => 'Good'],
                        ['value' => 2, 'label' => 'Satisfactory'],
                        ['value' => 1, 'label' => 'Unsatisfactory'],
                    ],
                ],
            ],
            'approval_steps' => [
                ['key' => 'employee_signoff', 'label' => 'Employee Signature', 'sequence' => 1, 'role_type' => 'employee', 'required' => true],
                ['key' => 'supervisor_signoff', 'label' => 'Supervisor Signature', 'sequence' => 2, 'role_type' => 'reporting_officer', 'required' => true],
                ['key' => 'authorized_official_signoff', 'label' => 'Authorized Official Signature', 'sequence' => 3, 'role_type' => 'authorized_official', 'required' => true],
                ['key' => 'permanent_secretary_signoff', 'label' => 'Permanent Secretary Signature', 'sequence' => 4, 'role_type' => 'permanent_secretary', 'required' => false],
            ],
        ];

        return self::normalizeSharedRowSections($definition);
    }

    private static function sharedRowSectionConfigs(): array
    {
        return [
            'performance_objectives' => [
                'template_managed_field_keys' => ['objective_category', 'objective', 'output', 'measure', 'target'],
                'template_parent_field_keys' => ['objective_category', 'objective'],
                'template_child_field_keys' => ['output', 'measure', 'target'],
                'plan_evaluation_field_keys' => ['score_out_of_10', 'supervisee_comment', 'supervisor_comment'],
                'row_heading_key' => 'objective',
                'row_identity_key' => 'objective',
                'child_row_heading_key' => 'output',
                'child_row_identity_key' => 'output',
            ],
            'coaching' => [
                'template_managed_field_keys' => ['development_objective', 'expected_result'],
            ],
            'behavioural_attributes' => [
                'template_managed_field_keys' => ['attribute_name', 'description', 'applicable'],
            ],
            'personal_development_goals' => [
                'template_managed_field_keys' => ['performance_gap', 'agreed_action', 'time_frame'],
            ],
            'development_objectives' => [
                'template_managed_field_keys' => ['development_objective', 'expected_result'],
            ],
            'personal_attributes' => [
                'template_managed_field_keys' => ['attribute_name', 'description'],
            ],
        ];
    }

    public static function normalizeSharedRowSections(array $definition, bool $blankRows = false): array
    {
        if (!is_array($definition['sections'] ?? null)) {
            return $definition;
        }

        $definition['sections'] = collect($definition['sections'])
            ->map(function (array $section) use ($blankRows): array {
                $config = self::sharedRowSectionConfig($section['key'] ?? null);
                if ($config === null) {
                    return $section;
                }

                $layout = is_array($section['layout_config_json'] ?? null) ? $section['layout_config_json'] : [];
                $seedRows = $blankRows
                    ? []
                    : (array) ($section['rows'] ?? $layout['seed_rows'] ?? []);

                unset($layout['seed_rows']);

                $section = self::normalizeBoundedSectionFieldDefinitions($section);

                $section['layout_config_json'] = array_merge($layout, [
                    'display' => $layout['display'] ?? 'accordion',
                    'row_source' => 'template_section_rows',
                    'template_managed_field_keys' => $config['template_managed_field_keys'],
                    'template_parent_field_keys' => $config['template_parent_field_keys'] ?? data_get($layout, 'template_parent_field_keys', []),
                    'template_child_field_keys' => $config['template_child_field_keys'] ?? data_get($layout, 'template_child_field_keys', []),
                    'plan_evaluation_field_keys' => $config['plan_evaluation_field_keys'] ?? data_get($layout, 'plan_evaluation_field_keys', []),
                    'allow_custom_entries' => false,
                ]);
                $normalizedRows = self::normalizeSharedRowDefinitions($seedRows);
                if (($section['key'] ?? null) === 'performance_objectives') {
                    $normalizedRows = self::normalizePerformanceObjectiveRows($normalizedRows);
                }

                $section['rows'] = $normalizedRows;

                return $section;
            })
            ->all();

        return $definition;
    }

    private static function normalizeBoundedSectionFieldDefinitions(array $section): array
    {
        if (($section['key'] ?? null) !== 'performance_objectives') {
            return $section;
        }

        $fields = collect($section['fields'] ?? [])
            ->filter(fn ($field) => is_array($field) && isset($field['key']))
            ->keyBy('key');

        $fields['objective_category'] = array_merge([
            'key' => 'objective_category',
            'label' => 'Category',
            'field_type' => 'select',
            'data_type' => 'string',
            'input_mode' => 'manual_entry',
            'required' => true,
            'options_json' => self::defaultPerformanceObjectiveCategoryOptions(),
            'sort_order' => 1,
        ], (array) ($fields->get('objective_category') ?? []));

        if (!is_array($fields['objective_category']['options_json'] ?? null) || $fields['objective_category']['options_json'] === []) {
            $fields['objective_category']['options_json'] = self::defaultPerformanceObjectiveCategoryOptions();
        }

        $sortOrderMap = [
            'objective_category' => 1,
            'objective' => 2,
            'output' => 3,
            'measure' => 4,
            'target' => 5,
            'score_out_of_10' => 6,
            'supervisee_comment' => 7,
            'supervisor_comment' => 8,
        ];

        $normalizedFields = $fields
            ->map(function (array $field) use ($sortOrderMap): array {
                if (isset($sortOrderMap[$field['key'] ?? ''])) {
                    $field['sort_order'] = $sortOrderMap[$field['key']];
                }

                return $field;
            })
            ->sortBy(fn (array $field): int => (int) ($field['sort_order'] ?? 999))
            ->values()
            ->all();

        $section['fields'] = $normalizedFields;

        return $section;
    }

    private static function normalizeSharedRowDefinitions(array $rows): array
    {
        return collect($rows)
            ->values()
            ->map(function ($row, int $index): ?array {
                if (!is_array($row)) {
                    return null;
                }

                return array_merge($row, [
                    'sort_order' => (int) ($row['sort_order'] ?? ($index + 1)),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function normalizePerformanceObjectiveRows(array $rows): array
    {
        return collect($rows)
            ->map(function (array $row): array {
                $values = (array) ($row['values_json'] ?? $row);
                $childRows = self::normalizeSharedRowDefinitions((array) ($row['child_rows'] ?? []));
                $detailValues = collect(['output', 'measure', 'target'])
                    ->filter(fn (string $key): bool => array_key_exists($key, $values) && trim((string) $values[$key]) !== '')
                    ->mapWithKeys(fn (string $key): array => [$key => $values[$key]])
                    ->all();

                if ($childRows === [] && $detailValues !== []) {
                    $childRows[] = [
                        'values_json' => $detailValues,
                        'sort_order' => 1,
                    ];
                }

                foreach (['output', 'measure', 'target'] as $key) {
                    unset($values[$key]);
                }

                $row['values_json'] = $values;
                $row['child_rows'] = $childRows;

                return $row;
            })
            ->values()
            ->all();
    }
}
