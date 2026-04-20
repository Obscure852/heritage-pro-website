<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Subject;
use App\Support\SyllabusSeedRegistry;
use App\Support\AcademicStructureRegistry;
use Illuminate\Support\Facades\Log;
use App\Models\SchoolSetup;

return new class extends Migration {
    const PRIMARY_SUBJECTS = [
        // Preschool Subjects
        ['abbrev' => 'GMD', 'name' => 'Gross Motor Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'FMD', 'name' => 'Fine Motor Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'NUM', 'name' => 'Numeracy', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'COD', 'name' => 'Cognitive Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'LND', 'name' => 'Language Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'SED', 'name' => 'Social and Emotional Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'LMD', 'name' => 'Large Muscle Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['abbrev' => 'SMD', 'name' => 'Small Muscle Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],

        // Middle School Subjects
        ['abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'Agriculture'],
        ['abbrev' => 'MATH', 'name' => 'Mathematics', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['abbrev' => 'ENG', 'name' => 'English', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'English'],
        ['abbrev' => 'SCI', 'name' => 'Science', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'Science'],
        ['abbrev' => 'SOC', 'name' => 'Social Studies', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'Social Studies'],
        ['abbrev' => 'SET', 'name' => 'Setswana', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'Foreign Language'],
        ['abbrev' => 'CAP', 'name' => 'CAPA', 'level' => 'Primary', 'components' => false, 'description' => '', 'department' => 'Physical Education'],
    ];

    const JUNIOR_SUBJECTS = [
        ['abbrev' => 'MATH', 'name' => 'Mathematics', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Mathematics & Science'],
        ['abbrev' => 'ENG', 'name' => 'English', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Languages'],
        ['abbrev' => 'SCI', 'name' => 'Science', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Mathematics & Science'],
        ['abbrev' => 'SOC', 'name' => 'Social Studies', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Social Studies'],
        ['abbrev' => 'SETS', 'name' => 'Setswana', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Languages'],
        ['abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Agriculture'],
        ['abbrev' => 'ME', 'name' => 'Moral Education', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Humanities'],
        
        ['abbrev' => 'ART', 'name' => 'Art', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Art'],
        ['abbrev' => 'DT', 'name' => 'Design & Technology', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Design & Technology'],
        ['abbrev' => 'MUS', 'name' => 'Music', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Music'],
        ['abbrev' => 'RE', 'name' => 'Religious Education', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Humanities'],
        ['abbrev' => 'PE', 'name' => 'Physical Education', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Humanities'],
        ['abbrev' => 'FR', 'name' => 'French', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Generals'],
        ['abbrev' => 'HE', 'name' => 'Home Economics', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Home Economics'],
        ['abbrev' => 'OP', 'name' => 'Office Procedures', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Office Procedures'],
        ['abbrev' => 'ACC', 'name' => 'Accounting', 'level' => 'Junior', 'components' => false, 'description' => '', 'department' => 'Accounting'],
    ];

    const SENIOR_SUBJECTS = [
        ['abbrev' => 'MATH', 'name' => 'Mathematics', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['abbrev' => 'MATH1', 'name' => 'Mathematics I', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['abbrev' => 'MATH2', 'name' => 'Mathematics II', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['abbrev' => 'ENG', 'name' => 'English', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'English'],
        ['abbrev' => 'CHI', 'name' => 'Chemistry', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Science'],
        ['abbrev' => 'PHY', 'name' => 'Physics', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Science'],
        ['abbrev' => 'BIO', 'name' => 'Biology', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Science'],
        ['abbrev' => 'DS', 'name' => 'Double Science', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Science'],
        ['abbrev' => 'BS', 'name' => 'Business Studies', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Business'],
        ['abbrev' => 'EXM', 'name' => 'Extended Mathematics', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['abbrev' => 'BM', 'name' => 'Business Management', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Business'],
        ['abbrev' => 'SETS', 'name' => 'Setswana', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Foreign Language'],
        ['abbrev' => 'CS', 'name' => 'Computer Studies', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Computer Studies'],
        ['abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Agriculture'],
        ['abbrev' => 'ME', 'name' => 'Moral Education', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Moral Education'],
        ['abbrev' => 'FF', 'name' => 'Fashion & Fabrics', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Fashion & Fabrics'],
        ['abbrev' => 'FN', 'name' => 'Food & Nutrition', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Food & Nutrition'],
        ['abbrev' => 'ART', 'name' => 'Art', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Art'],
        ['abbrev' => 'DT', 'name' => 'Design & Technology', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Design & Technology'],
        ['abbrev' => 'MUS', 'name' => 'Music', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Music'],
        ['abbrev' => 'PE', 'name' => 'Physical Education', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Physical Education'],
        ['abbrev' => 'RE', 'name' => 'Religious Education', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Religious Education'],
        ['abbrev' => 'HIS', 'name' => 'History', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'History'],
        ['abbrev' => 'GEO', 'name' => 'Geography', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Geography'],
        ['abbrev' => 'DVS', 'name' => 'Development Studies', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Development Studies'],
        ['abbrev' => 'ACC', 'name' => 'Accounting', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Accounting'],
        ['abbrev' => 'EL', 'name' => 'English Literature', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'English'],
        ['abbrev' => 'STA', 'name' => 'Statistics', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Statistics'],
        ['abbrev' => 'COMM', 'name' => 'Commerce', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Commerce'],
        ['abbrev' => 'ENT', 'name' => 'Entrepreneurship', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Entrepreneurship'],
        ['abbrev' => 'AMA', 'name' => 'Add Mathematics', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['abbrev' => 'SOS', 'name' => 'Social Studies', 'level' => 'Senior', 'components' => false, 'description' => '', 'department' => 'Social Studies'],
    ];

    public function up() {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('abbrev')->index();
            $table->string('name')->index();
            $table->string('canonical_key')->nullable()->index();
            $table->string('level')->index();
            $table->boolean('components');
            $table->string('description')->nullable();
            $table->string('department')->nullable();
            $table->string('syllabus_url', 2048)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        $school_type = SchoolSetup::value('type');
        $this->insertSubjects($school_type);
    }

    public function down() {
        Schema::dropIfExists('subjects');
    }

    private function insertSubjects($school_type) {
        try {
            foreach (AcademicStructureRegistry::subjectDefinitionsForMode(SchoolSetup::normalizeType($school_type)) as $subject) {
                Subject::create($this->buildSubjectPayload($subject));
            }
        } catch (\Exception $e) {
            Log::error('Failed to insert subjects: ' . $e->getMessage());
        }
    }

    private function buildSubjectPayload(array $subject): array
    {
        $subject['canonical_key'] = $subject['canonical_key'] ?? AcademicStructureRegistry::canonicalKeyFor(
            $subject['level'] ?? null,
            $subject['abbrev'] ?? null,
            $subject['name'] ?? null
        );
        $subject['syllabus_url'] = SyllabusSeedRegistry::urlFor(
            $subject['level'] ?? null,
            $subject['abbrev'] ?? null,
            $subject['name'] ?? null
        );

        return $subject;
    }
};
