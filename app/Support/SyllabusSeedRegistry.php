<?php

namespace App\Support;

class SyllabusSeedRegistry
{
    /**
     * @return array<int, array{level:string,abbrev:string,name:string,url:string}>
     */
    public static function subjectUrls(): array
    {
        return [
            ['level' => 'Junior', 'abbrev' => 'ACC', 'name' => 'Accounting', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/accounting_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'AGR', 'name' => 'Agriculture', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/agriculture_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'ART', 'name' => 'Art', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/art_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'DT', 'name' => 'Design & Technology', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/design_and_technology_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'ENG', 'name' => 'English', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/english_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'FR', 'name' => 'French', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/french_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'HE', 'name' => 'Home Economics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/home_economics_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/mathematics_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'ME', 'name' => 'Moral Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/moral_education_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'MUS', 'name' => 'Music', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/music_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'OP', 'name' => 'Office Procedures', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/office_procedures_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'PE', 'name' => 'Physical Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/physical_education_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'RE', 'name' => 'Religious Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/religious_education_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'SCI', 'name' => 'Science', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/science_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'SETS', 'name' => 'Setswana', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/setswana_syllabus.json'],
            ['level' => 'Junior', 'abbrev' => 'SOC', 'name' => 'Social Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/social_studies_syllabus.json'],

            ['level' => 'Senior', 'abbrev' => 'ACC', 'name' => 'Accounting', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/accounting_syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'ART', 'name' => 'Art', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/art_design_syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'BIO', 'name' => 'Biology', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/biology_syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'BS', 'name' => 'Business Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Business_Studies_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'CHI', 'name' => 'Chemistry', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Chemistry_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'COMM', 'name' => 'Commerce', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/commerce_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'CS', 'name' => 'Computer Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Computer_Studies_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'DT', 'name' => 'Design & Technology', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Design_and_Technology_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'DVS', 'name' => 'Development Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Development_Studies_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'ENG', 'name' => 'English', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/English_Language_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'FF', 'name' => 'Fashion & Fabrics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Fashion_and_Fabrics_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'FN', 'name' => 'Food & Nutrition', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Food_and_Nutrition_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'FR', 'name' => 'French', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/French_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'GEO', 'name' => 'Geography', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Geography_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'HIS', 'name' => 'History', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/history_syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'EL', 'name' => 'English Literature', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Literature_in_English_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Mathematics_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'ME', 'name' => 'Moral Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Moral_Education_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'MUS', 'name' => 'Music', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Music_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'PE', 'name' => 'Physical Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Physical_Education_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'PHY', 'name' => 'Physics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Physics_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'RE', 'name' => 'Religious Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/religious_education_syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'DS', 'name' => 'Double Science', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/science_double_award_syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'SETS', 'name' => 'Setswana', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Setswana_Syllabus.json'],
            ['level' => 'Senior', 'abbrev' => 'SOS', 'name' => 'Social Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Social_Studies_Syllabus.json'],
        ];
    }

    public static function urlFor(?string $level, ?string $abbrev = null, ?string $name = null): ?string
    {
        if (blank($level)) {
            return null;
        }

        $normalizedLevel = trim((string) $level);
        $normalizedAbbrev = blank($abbrev) ? null : strtoupper(trim((string) $abbrev));
        $normalizedName = self::normalizeName($name);

        foreach (self::subjectUrls() as $subject) {
            if ($subject['level'] !== $normalizedLevel) {
                continue;
            }

            if ($normalizedAbbrev && strtoupper($subject['abbrev']) === $normalizedAbbrev) {
                return $subject['url'];
            }

            if ($normalizedName && self::normalizeName($subject['name']) === $normalizedName) {
                return $subject['url'];
            }
        }

        return null;
    }

    /**
     * @return array<int, string>|null
     */
    public static function gradesForLevel(?string $level): ?array
    {
        return match (trim((string) $level)) {
            'Junior' => ['F1', 'F2', 'F3'],
            'Senior' => ['F4', 'F5'],
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function canonicalSyllabusPayload(int $subjectId, ?string $level, ?string $sourceUrl): ?array
    {
        $grades = self::gradesForLevel($level);

        if (!$grades || blank($sourceUrl)) {
            return null;
        }

        return [
            'subject_id' => $subjectId,
            'grades' => json_encode($grades, JSON_UNESCAPED_SLASHES),
            'level' => trim((string) $level),
            'document_id' => null,
            'is_active' => true,
            'description' => null,
            'source_url' => trim((string) $sourceUrl),
            'cached_structure' => null,
            'cached_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }

    private static function normalizeName(?string $name): ?string
    {
        if (blank($name)) {
            return null;
        }

        $normalized = strtolower(trim((string) $name));
        $normalized = str_replace('&', 'and', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized ?: null;
    }
}
