<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        $now = now();

        // ==================== CATEGORIES ====================
        // Parent categories first, then children referencing parent IDs

        $parentCategories = [
            [
                'name' => 'Academic',
                'slug' => 'academic',
                'description' => 'Academic documents including curricula, syllabi, and teaching materials',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#4e73df',
                'sort_order' => 1,
                'retention_days' => 2555, // 7 years
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Administrative',
                'slug' => 'administrative',
                'description' => 'School administrative documents, memos, and circulars',
                'icon' => 'fas fa-building',
                'color' => '#1cc88a',
                'sort_order' => 2,
                'retention_days' => 2555,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Student Records',
                'slug' => 'student-records',
                'description' => 'Student-related documents and records',
                'icon' => 'fas fa-user-graduate',
                'color' => '#36b9cc',
                'sort_order' => 3,
                'retention_days' => 3650, // 10 years
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Staff Records',
                'slug' => 'staff-records',
                'description' => 'Staff-related documents, contracts, and HR records',
                'icon' => 'fas fa-users',
                'color' => '#f6c23e',
                'sort_order' => 4,
                'retention_days' => 3650,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Financial',
                'slug' => 'financial',
                'description' => 'Financial documents, budgets, and fee-related records',
                'icon' => 'fas fa-money-bill-wave',
                'color' => '#e74a3b',
                'sort_order' => 5,
                'retention_days' => 2555,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Policies & Procedures',
                'slug' => 'policies-procedures',
                'description' => 'School policies, procedures, and governance documents',
                'icon' => 'fas fa-gavel',
                'color' => '#858796',
                'sort_order' => 6,
                'retention_days' => null, // Permanent
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Communications',
                'slug' => 'communications',
                'description' => 'Letters, newsletters, and notices sent to parents and stakeholders',
                'icon' => 'fas fa-envelope',
                'color' => '#5a5c69',
                'sort_order' => 7,
                'retention_days' => 1095, // 3 years
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Forms & Templates',
                'slug' => 'forms-templates',
                'description' => 'Reusable forms, templates, and standard documents',
                'icon' => 'fas fa-file-alt',
                'color' => '#6f42c1',
                'sort_order' => 8,
                'retention_days' => null,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('document_categories')->insert($parentCategories);

        // Fetch parent IDs for child categories
        $parentIds = DB::table('document_categories')
            ->whereNull('parent_id')
            ->pluck('id', 'slug')
            ->toArray();

        $childCategories = [
            // Academic children
            [
                'name' => 'Schemes of Work',
                'slug' => 'schemes-of-work',
                'description' => 'Teaching schemes of work and lesson plans',
                'parent_id' => $parentIds['academic'],
                'icon' => 'fas fa-book',
                'color' => '#4e73df',
                'sort_order' => 1,
                'retention_days' => 1825, // 5 years
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Examinations',
                'slug' => 'examinations',
                'description' => 'Exam papers, mark schemes, and examination records',
                'parent_id' => $parentIds['academic'],
                'icon' => 'fas fa-clipboard-check',
                'color' => '#4e73df',
                'sort_order' => 2,
                'retention_days' => 2555,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Curricula & Syllabi',
                'slug' => 'curricula-syllabi',
                'description' => 'Curriculum documents and subject syllabi',
                'parent_id' => $parentIds['academic'],
                'icon' => 'fas fa-list-alt',
                'color' => '#4e73df',
                'sort_order' => 3,
                'retention_days' => null,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Report Cards',
                'slug' => 'report-cards',
                'description' => 'Student report cards and academic progress reports',
                'parent_id' => $parentIds['academic'],
                'icon' => 'fas fa-chart-bar',
                'color' => '#4e73df',
                'sort_order' => 4,
                'retention_days' => 3650,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Administrative children
            [
                'name' => 'Memos & Circulars',
                'slug' => 'memos-circulars',
                'description' => 'Internal memos and circulars',
                'parent_id' => $parentIds['administrative'],
                'icon' => 'fas fa-bullhorn',
                'color' => '#1cc88a',
                'sort_order' => 1,
                'retention_days' => 1095,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Minutes & Reports',
                'slug' => 'minutes-reports',
                'description' => 'Meeting minutes and administrative reports',
                'parent_id' => $parentIds['administrative'],
                'icon' => 'fas fa-clipboard',
                'color' => '#1cc88a',
                'sort_order' => 2,
                'retention_days' => 2555,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Timetables',
                'slug' => 'timetables',
                'description' => 'Class and school timetables',
                'parent_id' => $parentIds['administrative'],
                'icon' => 'fas fa-calendar-alt',
                'color' => '#1cc88a',
                'sort_order' => 3,
                'retention_days' => 1095,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Student Records children
            [
                'name' => 'Admission Documents',
                'slug' => 'admission-documents',
                'description' => 'Student admission forms and supporting documents',
                'parent_id' => $parentIds['student-records'],
                'icon' => 'fas fa-file-signature',
                'color' => '#36b9cc',
                'sort_order' => 1,
                'retention_days' => 3650,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Transfer Documents',
                'slug' => 'transfer-documents',
                'description' => 'Student transfer letters and clearance forms',
                'parent_id' => $parentIds['student-records'],
                'icon' => 'fas fa-exchange-alt',
                'color' => '#36b9cc',
                'sort_order' => 2,
                'retention_days' => 3650,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Disciplinary Records',
                'slug' => 'disciplinary-records',
                'description' => 'Student disciplinary reports and records',
                'parent_id' => $parentIds['student-records'],
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#36b9cc',
                'sort_order' => 3,
                'retention_days' => 2555,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Staff Records children
            [
                'name' => 'Contracts & Agreements',
                'slug' => 'contracts-agreements',
                'description' => 'Staff employment contracts and agreements',
                'parent_id' => $parentIds['staff-records'],
                'icon' => 'fas fa-file-contract',
                'color' => '#f6c23e',
                'sort_order' => 1,
                'retention_days' => 3650,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Qualifications & Certifications',
                'slug' => 'qualifications-certifications',
                'description' => 'Staff qualification documents and certifications',
                'parent_id' => $parentIds['staff-records'],
                'icon' => 'fas fa-certificate',
                'color' => '#f6c23e',
                'sort_order' => 2,
                'retention_days' => null,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Leave & Attendance',
                'slug' => 'leave-attendance',
                'description' => 'Staff leave applications and attendance records',
                'parent_id' => $parentIds['staff-records'],
                'icon' => 'fas fa-clock',
                'color' => '#f6c23e',
                'sort_order' => 3,
                'retention_days' => 1825,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Financial children
            [
                'name' => 'Budgets',
                'slug' => 'budgets',
                'description' => 'Annual budgets and budget proposals',
                'parent_id' => $parentIds['financial'],
                'icon' => 'fas fa-chart-pie',
                'color' => '#e74a3b',
                'sort_order' => 1,
                'retention_days' => 2555,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Invoices & Receipts',
                'slug' => 'invoices-receipts',
                'description' => 'Fee invoices, receipts, and payment records',
                'parent_id' => $parentIds['financial'],
                'icon' => 'fas fa-receipt',
                'color' => '#e74a3b',
                'sort_order' => 2,
                'retention_days' => 2555,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Audit Reports',
                'slug' => 'audit-reports',
                'description' => 'Financial audit reports and statements',
                'parent_id' => $parentIds['financial'],
                'icon' => 'fas fa-search-dollar',
                'color' => '#e74a3b',
                'sort_order' => 3,
                'retention_days' => 3650,
                'requires_approval' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Communications children
            [
                'name' => 'Parent Letters',
                'slug' => 'parent-letters',
                'description' => 'Letters and notices sent to parents',
                'parent_id' => $parentIds['communications'],
                'icon' => 'fas fa-envelope-open-text',
                'color' => '#5a5c69',
                'sort_order' => 1,
                'retention_days' => 1095,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Newsletters',
                'slug' => 'newsletters',
                'description' => 'School newsletters and bulletins',
                'parent_id' => $parentIds['communications'],
                'icon' => 'fas fa-newspaper',
                'color' => '#5a5c69',
                'sort_order' => 2,
                'retention_days' => 1095,
                'requires_approval' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('document_categories')->insert($childCategories);

        // ==================== TAGS ====================
        // All tags are official (admin-created) system defaults

        $tags = [
            // Status/Priority tags
            ['name' => 'Urgent', 'slug' => 'urgent', 'description' => 'Time-sensitive documents requiring immediate attention', 'color' => '#e74a3b', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Important', 'slug' => 'important', 'description' => 'High-priority documents', 'color' => '#f6c23e', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Draft', 'slug' => 'draft', 'description' => 'Documents still in draft stage', 'color' => '#858796', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Final', 'slug' => 'final', 'description' => 'Finalised and approved documents', 'color' => '#1cc88a', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],

            // Academic term/year tags
            ['name' => 'Term 1', 'slug' => 'term-1', 'description' => 'Documents related to Term 1', 'color' => '#4e73df', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Term 2', 'slug' => 'term-2', 'description' => 'Documents related to Term 2', 'color' => '#4e73df', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Term 3', 'slug' => 'term-3', 'description' => 'Documents related to Term 3', 'color' => '#4e73df', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],

            // School type tags
            ['name' => 'Senior School', 'slug' => 'senior-school', 'description' => 'Documents for senior school', 'color' => '#36b9cc', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Junior/CJSS', 'slug' => 'junior-cjss', 'description' => 'Documents for junior secondary / CJSS', 'color' => '#36b9cc', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Primary School', 'slug' => 'primary-school', 'description' => 'Documents for primary school', 'color' => '#36b9cc', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Reception/Pre-school', 'slug' => 'reception-pre-school', 'description' => 'Documents for reception / pre-school', 'color' => '#36b9cc', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],

            // Audience tags
            ['name' => 'Staff Only', 'slug' => 'staff-only', 'description' => 'Documents intended for staff only', 'color' => '#fd7e14', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Parents', 'slug' => 'parents', 'description' => 'Documents intended for parents', 'color' => '#6f42c1', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Students', 'slug' => 'students', 'description' => 'Documents intended for students', 'color' => '#20c997', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Board/Governors', 'slug' => 'board-governors', 'description' => 'Documents for the school board or governors', 'color' => '#6610f2', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],

            // Document type tags
            ['name' => 'Template', 'slug' => 'template', 'description' => 'Reusable document templates', 'color' => '#17a2b8', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Confidential', 'slug' => 'confidential', 'description' => 'Confidential documents with restricted access', 'color' => '#dc3545', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Archived', 'slug' => 'archived', 'description' => 'Archived documents for historical reference', 'color' => '#6c757d', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ministry/BEC', 'slug' => 'ministry-bec', 'description' => 'Documents from or for Ministry of Education / BEC', 'color' => '#007bff', 'is_official' => true, 'usage_count' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('document_tags')->insert($tags);
    }

    public function down(): void {
        // Remove seeded tags (only official ones with no usage)
        DB::table('document_tags')
            ->where('is_official', true)
            ->where('usage_count', 0)
            ->whereNull('created_by_user_id')
            ->delete();

        // Remove child categories first, then parents
        DB::table('document_categories')
            ->whereNotNull('parent_id')
            ->delete();

        DB::table('document_categories')
            ->whereNull('parent_id')
            ->delete();
    }
};
