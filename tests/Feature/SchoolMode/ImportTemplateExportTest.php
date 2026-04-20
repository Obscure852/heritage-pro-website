<?php

namespace Tests\Feature\SchoolMode;

use App\Exports\ImportTemplateExport;
use App\Models\SchoolSetup;
use Tests\TestCase;

class ImportTemplateExportTest extends TestCase
{
    public function test_pref3_student_template_includes_psle_columns_but_primary_does_not(): void
    {
        $pref3Template = ImportTemplateExport::students(SchoolSetup::TYPE_PRE_F3);
        $primaryTemplate = ImportTemplateExport::students(SchoolSetup::TYPE_PRIMARY);

        $this->assertContains('overall_grade', $pref3Template->headings());
        $this->assertContains('mathematics_grade', $pref3Template->headings());
        $this->assertNotContains('ov', $pref3Template->headings());
        $this->assertSame('F1', $pref3Template->array()[0][10]);

        $this->assertNotContains('overall_grade', $primaryTemplate->headings());
        $this->assertSame('STD 1', $primaryTemplate->array()[0][10]);
    }

    public function test_k12_student_template_includes_both_psle_and_jce_columns(): void
    {
        $k12Template = ImportTemplateExport::students(SchoolSetup::TYPE_K12);

        $this->assertContains('overall_grade', $k12Template->headings());
        $this->assertContains('mathematics_grade', $k12Template->headings());
        $this->assertContains('ov', $k12Template->headings());
        $this->assertContains('ss', $k12Template->headings());
        $this->assertSame('F1', $k12Template->array()[0][10]);
    }

    public function test_junior_senior_student_template_defaults_to_middle_school_format_when_requested_directly(): void
    {
        $template = ImportTemplateExport::students(SchoolSetup::TYPE_JUNIOR_SENIOR);

        $this->assertContains('overall_grade', $template->headings());
        $this->assertContains('mathematics_grade', $template->headings());
        $this->assertNotContains('ov', $template->headings());
        $this->assertSame('F1', $template->array()[0][10]);
    }
}
