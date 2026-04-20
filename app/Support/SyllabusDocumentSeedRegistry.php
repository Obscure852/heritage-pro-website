<?php

namespace App\Support;

class SyllabusDocumentSeedRegistry
{
    /**
     * This registry is intentionally separate from the structured syllabus JSON
     * registry so document-backed syllabus links can diverge later without
     * changing the documents integration points.
     *
     * @return array<int, array{level:string,abbrev:string,name:string,url:string}>
     */
    public static function documentUrls(): array
    {
        return [
            ['level' => 'Junior', 'abbrev' => 'ACC', 'name' => 'Accounting', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/ACCOUNTING.pdf'],
            ['level' => 'Junior', 'abbrev' => 'AGR', 'name' => 'Agriculture', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/AGRICULTURE.pdf'],
            ['level' => 'Junior', 'abbrev' => 'ART', 'name' => 'Art', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/ART.pdf'],
            ['level' => 'Junior', 'abbrev' => 'CA', 'name' => 'Computer Awareness', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/COMPUTER+AWARENESS.pdf'],
            ['level' => 'Junior', 'abbrev' => 'DT', 'name' => 'Design & Technology', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/DESIGN+%26+TECHNOLOGY+2008.pdf'],
            ['level' => 'Junior', 'abbrev' => 'ENG', 'name' => 'English', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/ENGLISH.pdf'],
            ['level' => 'Junior', 'abbrev' => 'FR', 'name' => 'French', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/FRENCH.pdf'],
            ['level' => 'Junior', 'abbrev' => 'HE', 'name' => 'Home Economics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/HOME+ECONOMICS.pdf'],
            ['level' => 'Junior', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/MATHEMATICS.pdf'],
            ['level' => 'Junior', 'abbrev' => 'ME', 'name' => 'Moral Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/MORAL+EDUCATION.pdf'],
            ['level' => 'Junior', 'abbrev' => 'MUS', 'name' => 'Music', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/MUSIC.pdf'],
            ['level' => 'Junior', 'abbrev' => 'OP', 'name' => 'Office Procedures', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/OFFICE+PROCEDURES.pdf'],
            ['level' => 'Junior', 'abbrev' => 'PE', 'name' => 'Physical Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/PHYSICAL+EDUCATION.pdf'],
            ['level' => 'Junior', 'abbrev' => 'RE', 'name' => 'Religious Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/RELIGIOUS+EDUCATION.pdf'],
            ['level' => 'Junior', 'abbrev' => 'SCI', 'name' => 'Science', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/SCIENCE.pdf'],
            ['level' => 'Junior', 'abbrev' => 'SETS', 'name' => 'Setswana', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/SETSWANA.pdf'],
            ['level' => 'Junior', 'abbrev' => 'SOC', 'name' => 'Social Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/documents/SOCIAL+STUDIES.pdf'],

            ['level' => 'Senior', 'abbrev' => 'ACC', 'name' => 'Accounting', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Acounting.pdf'],
            ['level' => 'Senior', 'abbrev' => 'ART', 'name' => 'Art', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Art+%26+Design.pdf'],
            ['level' => 'Senior', 'abbrev' => 'BIO', 'name' => 'Biology', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Biology.pdf'],
            ['level' => 'Senior', 'abbrev' => 'BS', 'name' => 'Business Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Business+Studies.pdf'],
            ['level' => 'Senior', 'abbrev' => 'CHI', 'name' => 'Chemistry', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Chemistry.pdf'],
            ['level' => 'Senior', 'abbrev' => 'COMM', 'name' => 'Commerce', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Commerce.pdf'],
            ['level' => 'Senior', 'abbrev' => 'CS', 'name' => 'Computer Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Computer+Studies.pdf'],
            ['level' => 'Senior', 'abbrev' => 'DT', 'name' => 'Design & Technology', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Design+%26Technoolgy.pdf'],
            ['level' => 'Senior', 'abbrev' => 'DVS', 'name' => 'Development Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Development+studies.pdf'],
            ['level' => 'Senior', 'abbrev' => 'ENG', 'name' => 'English', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/English+Language.pdf'],
            ['level' => 'Senior', 'abbrev' => 'FF', 'name' => 'Fashion & Fabrics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Fashion+and+Fabrics.pdf'],
            ['level' => 'Senior', 'abbrev' => 'FN', 'name' => 'Food & Nutrition', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Food+and+Nutrition.pdf'],
            ['level' => 'Senior', 'abbrev' => 'FR', 'name' => 'French', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/French+Final+2010.pdf'],
            ['level' => 'Senior', 'abbrev' => 'GEO', 'name' => 'Geography', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Geography.pdf'],
            ['level' => 'Senior', 'abbrev' => 'GC', 'name' => 'Guidance & Counselling', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/guidance%26concelling.pdf'],
            ['level' => 'Senior', 'abbrev' => 'HIS', 'name' => 'History', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/History.pdf'],
            ['level' => 'Senior', 'abbrev' => 'HM', 'name' => 'Home Management', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Home+Management.pdf'],
            ['level' => 'Senior', 'abbrev' => 'EL', 'name' => 'English Literature', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Literature+in+English.pdf'],
            ['level' => 'Senior', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Mathematics.pdf'],
            ['level' => 'Senior', 'abbrev' => 'ME', 'name' => 'Moral Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/MORAL+EDUCATION+FINAL.pdf'],
            ['level' => 'Senior', 'abbrev' => 'MUS', 'name' => 'Music', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/MUSIC+FINAL.pdf'],
            ['level' => 'Senior', 'abbrev' => 'PE', 'name' => 'Physical Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/PHYSICAL+EDUCATION+FINAL.pdf'],
            ['level' => 'Senior', 'abbrev' => 'PHY', 'name' => 'Physics', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Physics.pdf'],
            ['level' => 'Senior', 'abbrev' => 'RE', 'name' => 'Religious Education', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Religious+Education.pdf'],
            ['level' => 'Senior', 'abbrev' => 'DS', 'name' => 'Double Science', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Science+Double+Award.pdf'],
            ['level' => 'Senior', 'abbrev' => 'SSA', 'name' => 'Science Single Award', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Science+Single+Award.pdf'],
            ['level' => 'Senior', 'abbrev' => 'SETS', 'name' => 'Setswana', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Setswana.pdf'],
            ['level' => 'Senior', 'abbrev' => 'SOS', 'name' => 'Social Studies', 'url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/Social_Studies.pdf'],
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

        foreach (self::documentUrls() as $subject) {
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

    public static function titleFor(string $subjectName, string $level): string
    {
        return trim($subjectName . ' ' . $level . ' Syllabus');
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
