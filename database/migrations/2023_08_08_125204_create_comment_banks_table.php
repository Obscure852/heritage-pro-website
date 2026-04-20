<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(){
        Schema::create('comment_banks', function (Blueprint $table) {
            $table->id();
            $table->integer('min_points');
            $table->integer('max_points');
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });

        $schoolType = DB::table('school_setup')->value('type');

        if (strtolower($schoolType) === 'Senior') {
            $this->populateSeniorCommentBank();
        } elseif (strtolower($schoolType) === 'Junior') {
            $this->populateJuniorCommentBank();
        } else {
            $this->populateJuniorCommentBank();
        }
    }

    public function down(){
        Schema::dropIfExists('comment_banks');
    }

    private function populateSeniorCommentBank(){
        $comments = [
            // (44-48): Outstanding performance
            ['min' => 44, 'max' => 48, 'comments' => [
                "Demonstrates exceptional mastery and innovative thinking.",
                "Shows superior analytical skills and deep understanding.",
                "Exhibits exemplary comprehension and application.",
                "Displays outstanding critical thinking and problem-solving.",
                "Shows remarkable academic ability and intellectual depth.",
                "Demonstrates excellent mastery and scholarly approach.",
                "Exhibits outstanding capabilities and academic leadership.",
                "Shows excellent comprehension and scholarly excellence."
            ]],
            // (41-43): Excellent performance (A Grade)
            ['min' => 41, 'max' => 43, 'comments' => [
                "Shows strong analytical skills and subject mastery.",
                "Demonstrates thorough understanding and application.",
                "Exhibits consistent academic effort and focus.",
                "Shows promising scholarly potential and dedication.",
                "Demonstrates a methodical approach to learning.",
                "Shows good subject mastery and comprehension.",
                "Displays reliable performance and commitment.",
                "Shows a strong academic foundation and progress."
            ]],
            // (31-40): Good performance with room to grow
            ['min' => 31, 'max' => 40, 'comments' => [
                "Shows growing mastery and understanding.",
                "Demonstrates advancing comprehension and skills.",
                "Exhibits steady improvement and dedication.",
                "Shows developing potential and consistent effort.",
                "Demonstrates progress in key academic areas.",
                "Shows enhanced understanding and capability.",
                "Displays academic growth and determination.",
                "Shows strengthening skills and focus."
            ]],
            // (21-30): Below average performance requiring focused improvement
            ['min' => 21, 'max' => 30, 'comments' => [
                "Shows moderate understanding and effort.",
                "Demonstrates basic comprehension with room to grow.",
                "Exhibits some progress but needs consistency.",
                "Shows potential with increased focus and practice.",
                "Demonstrates average performance and application.",
                "Shows foundational knowledge with areas to improve.",
                "Displays effort that can benefit from refinement.",
                "Shows satisfactory progress with further support."
            ]],
            // (11-20): Poor performance needing substantial improvement
            ['min' => 11, 'max' => 20, 'comments' => [
                "Needs significant improvement and focus.",
                "Requires additional support and effort.",
                "Shows minimal understanding currently.",
                "Needs focused attention and practice.",
                "Requires intensive support to improve performance.",
                "Shows limited comprehension of key concepts.",
                "Needs substantial effort to meet expectations.",
                "Requires dedicated work to strengthen skills."
            ]],
            // (0-10): Very poor performance requiring urgent intervention
            ['min' => 0, 'max' => 10, 'comments' => [
                "Needs urgent attention and support.",
                "Requires immediate academic intervention.",
                "Shows significant gaps in understanding.",
                "Needs comprehensive support and focus.",
                "Requires focused intervention and effort.",
                "Shows critical gaps in knowledge.",
                "Needs fundamental review and practice.",
                "Requires intensive support to improve."
            ]]
        ];

        foreach ($comments as $range) {
            foreach ($range['comments'] as $comment) {
                DB::table('comment_banks')->insert([
                    'min_points' => $range['min'],
                    'max_points' => $range['max'],
                    'body'       => $comment,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function populateJuniorCommentBank(){
        $comments = [
            // Merit (63-100)
            ['min' => 63, 'max' => 100, 'comments' => [
                "Demonstrates exceptional mastery and innovative thinking.",
                "Shows superior analytical skills and deep understanding.",
                "Exhibits exemplary comprehension and application.",
                "Displays outstanding critical thinking and problem-solving.",
                "Shows remarkable academic ability and intellectual depth.",
                "Demonstrates excellent mastery and scholarly approach.",
                "Exhibits outstanding capabilities and academic leadership.",
                "Shows excellent comprehension and scholarly excellence."
            ]],
            // A Grade (55-62)
            ['min' => 55, 'max' => 56, 'comments' => [
                "Shows strong analytical skills and subject mastery.",
                "Demonstrates thorough understanding and application.",
                "Exhibits consistent academic effort and focus.",
                "Shows promising scholarly potential and dedication.",
                "Demonstrates methodical approach to learning.",
                "Shows good subject mastery and comprehension.",
                "Displays reliable performance and commitment.",
                "Shows strong academic foundation and progress."
            ]],
            ['min' => 57, 'max' => 58, 'comments' => [
                "Shows growing mastery and understanding.",
                "Demonstrates advancing comprehension and skills.",
                "Exhibits steady improvement and dedication.",
                "Shows developing excellence and potential.",
                "Demonstrates consistent progress and effort.",
                "Shows enhanced understanding and capability.",
                "Displays academic growth and determination.",
                "Shows strengthening capabilities and focus."
            ]],
            ['min' => 59, 'max' => 60, 'comments' => [
                "Shows advanced understanding and analysis.",
                "Demonstrates sophisticated analytical skills.",
                "Exhibits excellent comprehension and insight.",
                "Shows strong academic prowess and dedication.",
                "Demonstrates notable achievement and effort.",
                "Shows impressive mastery of concepts.",
                "Displays commendable effort and growth.",
                "Shows excellent progress and potential."
            ]],
            ['min' => 61, 'max' => 62, 'comments' => [
                "Approaching merit level with strong performance.",
                "Demonstrates near-perfect understanding.",
                "Exhibits outstanding knowledge and skills.",
                "Shows exceptional capability and dedication.",
                "Demonstrates superior comprehension.",
                "Shows remarkable mastery and focus.",
                "Displays excellent achievement consistently.",
                "Shows distinguished performance and effort."
            ]],
            // B Grade (41-54)
            ['min' => 41, 'max' => 44, 'comments' => [
                "Shows solid understanding and effort.",
                "Demonstrates clear comprehension of concepts.",
                "Exhibits steady progress and dedication.",
                "Shows consistent effort and improvement.",
                "Demonstrates good grasp of material.",
                "Shows reliable knowledge and application.",
                "Displays steady work and commitment.",
                "Shows promising development and focus."
            ]],
            ['min' => 45, 'max' => 48, 'comments' => [
                "Shows growing understanding and potential.",
                "Demonstrates improving skills consistently.",
                "Exhibits good development and effort.",
                "Shows steady improvement and dedication.",
                "Demonstrates consistent effort and focus.",
                "Shows strengthening grasp of concepts.",
                "Displays advancing knowledge and skills.",
                "Shows good progress and commitment."
            ]],
            ['min' => 49, 'max' => 51, 'comments' => [
                "Shows competent understanding and effort.",
                "Demonstrates good mastery of concepts.",
                "Exhibits solid knowledge and dedication.",
                "Shows reliable comprehension and focus.",
                "Demonstrates steady progress and growth.",
                "Shows good capability and potential.",
                "Displays consistent effort and improvement.",
                "Shows promising ability and commitment."
            ]],
            ['min' => 52, 'max' => 54, 'comments' => [
                "Shows strong potential and dedication.",
                "Demonstrates advancing skills consistently.",
                "Exhibits good progress and focus.",
                "Shows solid achievement and effort.",
                "Demonstrates good understanding and growth.",
                "Shows steady improvement and mastery.",
                "Displays growing mastery of concepts.",
                "Shows commendable effort and progress."
            ]],
            // C Grade (28-40)
            ['min' => 28, 'max' => 31, 'comments' => [
                "Shows basic understanding and effort.",
                "Demonstrates foundational knowledge.",
                "Exhibits developing skills and focus.",
                "Shows ongoing effort and improvement.",
                "Demonstrates basic grasp of concepts.",
                "Shows steady work and dedication.",
                "Displays consistent attempt to improve.",
                "Shows growing comprehension and effort."
            ]],
            ['min' => 32, 'max' => 35, 'comments' => [
                "Shows improving understanding and focus.",
                "Demonstrates developing mastery of basics.",
                "Exhibits steady effort and dedication.",
                "Shows consistent work and progress.",
                "Demonstrates growing knowledge base.",
                "Shows ongoing progress and commitment.",
                "Displays developing skills and effort.",
                "Shows steady improvement and potential."
            ]],
            ['min' => 36, 'max' => 40, 'comments' => [
                "Shows potential for further growth.",
                "Demonstrates steady progress and effort.",
                "Exhibits consistent effort to improve.",
                "Shows developing capability and focus.",
                "Demonstrates improving grasp of concepts.",
                "Shows growing understanding and dedication.",
                "Displays steady development and work.",
                "Shows advancing comprehension and effort."
            ]],
            // D Grade (14-27)
            ['min' => 14, 'max' => 17, 'comments' => [
                "Needs improvement and focused effort.",
                "Demonstrates limited understanding currently.",
                "Exhibits developing knowledge base.",
                "Shows initial comprehension of basics.",
                "Demonstrates basic attempt at concepts.",
                "Shows foundational work and effort.",
                "Displays emerging skills and potential.",
                "Shows beginning grasp of material."
            ]],
            ['min' => 18, 'max' => 22, 'comments' => [
                "Shows increasing effort and focus.",
                "Demonstrates growing understanding.",
                "Exhibits improving work and dedication.",
                "Shows steady attempt to progress.",
                "Demonstrates ongoing progress and effort.",
                "Shows developing skills and potential.",
                "Displays improving grasp of concepts.",
                "Shows gradual progress and commitment."
            ]],
            ['min' => 23, 'max' => 27, 'comments' => [
                "Shows potential for improvement.",
                "Demonstrates improvement with effort.",
                "Exhibits growing effort and focus.",
                "Shows steady progress in learning.",
                "Demonstrates development of skills.",
                "Shows consistent attempt to improve.",
                "Displays improving work and dedication.",
                "Shows advancing effort and potential."
            ]],
            // E Grade (8-13)
            ['min' => 8, 'max' => 13, 'comments' => [
                "Needs significant improvement and focus.",
                "Requires additional support and effort.",
                "Shows minimal understanding currently.",
                "Needs focused attention and practice.",
                "Requires intensive support and work.",
                "Shows limited comprehension of basics.",
                "Needs substantial improvement effort.",
                "Requires dedicated work and focus."
            ]],
            // U Grade (0-7)
            ['min' => 0, 'max' => 7, 'comments' => [
                "Needs urgent attention and support.",
                "Requires immediate academic intervention.",
                "Shows significant gaps in understanding.",
                "Needs comprehensive support and focus.",
                "Requires focused intervention and effort.",
                "Shows critical gaps in knowledge.",
                "Needs fundamental review and practice.",
                "Requires intensive support and work."
            ]]
        ];

        foreach ($comments as $range) {
            foreach ($range['comments'] as $comment) {
                DB::table('comment_banks')->insert([
                    'min_points' => $range['min'],
                    'max_points' => $range['max'],
                    'body'       => $comment,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
};
