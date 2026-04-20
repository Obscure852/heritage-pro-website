<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up() {
        $qualifications = [
            // Teaching Certificates & Diplomas
            ['qualification_code' => 'PTC', 'qualification' => 'Primary Teaching Certificate'],
            ['qualification_code' => 'DPE', 'qualification' => 'Diploma in Primary Education'],
            ['qualification_code' => 'DSE', 'qualification' => 'Diploma in Secondary Education'],
            ['qualification_code' => 'DE', 'qualification' => 'Diploma in Education'],
            ['qualification_code' => 'PGDE', 'qualification' => 'Postgraduate Diploma in Education'],
            ['qualification_code' => 'DECE', 'qualification' => 'Diploma in Early Childhood Education'],
            ['qualification_code' => 'ACE', 'qualification' => 'Advanced Certificate in Education'],
            ['qualification_code' => 'PGCE', 'qualification' => 'Postgraduate Certificate in Education'],
            ['qualification_code' => 'CSEN', 'qualification' => 'Certificate in Special Education Needs'],
            ['qualification_code' => 'DTVET', 'qualification' => 'Diploma in Technical and Vocational Education and Training'],

            // Education Degrees
            ['qualification_code' => 'BEd', 'qualification' => 'Bachelor of Education'],
            ['qualification_code' => 'MEd', 'qualification' => 'Master of Education'],
            ['qualification_code' => 'EdD', 'qualification' => 'Doctor of Education'],

            // General Degrees (held by teachers with subject specialisations)
            ['qualification_code' => 'BA', 'qualification' => 'Bachelor of Arts'],
            ['qualification_code' => 'BSc', 'qualification' => 'Bachelor of Science'],
            ['qualification_code' => 'BCom', 'qualification' => 'Bachelor of Commerce'],
            ['qualification_code' => 'BBA', 'qualification' => 'Bachelor of Business Administration'],
            ['qualification_code' => 'BSW', 'qualification' => 'Bachelor of Social Work'],
            ['qualification_code' => 'BNS', 'qualification' => 'Bachelor of Nursing Science'],
            ['qualification_code' => 'LLB', 'qualification' => 'Bachelor of Laws'],
            ['qualification_code' => 'BEng', 'qualification' => 'Bachelor of Engineering'],

            // Postgraduate Degrees
            ['qualification_code' => 'MA', 'qualification' => 'Master of Arts'],
            ['qualification_code' => 'MSc', 'qualification' => 'Master of Science'],
            ['qualification_code' => 'MBA', 'qualification' => 'Master of Business Administration'],
            ['qualification_code' => 'MPhil', 'qualification' => 'Master of Philosophy'],
            ['qualification_code' => 'PhD', 'qualification' => 'Doctor of Philosophy'],

            // Professional Certificates
            ['qualification_code' => 'CIPS', 'qualification' => 'Chartered Institute of Procurement and Supply'],
            ['qualification_code' => 'ACCA', 'qualification' => 'Association of Chartered Certified Accountants'],
            ['qualification_code' => 'CPA', 'qualification' => 'Certified Public Accountant'],
            ['qualification_code' => 'ICDL', 'qualification' => 'International Computer Driving Licence'],
        ];

        $now = now();

        foreach ($qualifications as $qual) {
            DB::table('qualifications')->updateOrInsert(
                ['qualification_code' => $qual['qualification_code']],
                [
                    'qualification' => $qual['qualification'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down() {
        $codes = [
            'PTC', 'DPE', 'DSE', 'DE', 'PGDE', 'DECE', 'ACE', 'PGCE', 'CSEN', 'DTVET',
            'BEd', 'MEd', 'EdD',
            'BA', 'BSc', 'BCom', 'BBA', 'BSW', 'BNS', 'LLB', 'BEng',
            'MA', 'MSc', 'MBA', 'MPhil', 'PhD',
            'CIPS', 'ACCA', 'CPA', 'ICDL',
        ];

        DB::table('qualifications')
            ->whereIn('qualification_code', $codes)
            ->whereNull('deleted_at')
            ->delete();
    }
};
