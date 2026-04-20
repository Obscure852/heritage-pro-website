<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{

    public function up(): void{
        Schema::create('score_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('min_score')->comment('Inclusive lower bound for the score range');
            $table->integer('max_score')->comment('Inclusive upper bound for the score range');
            $table->text('comment')->comment('Comment text for this range');
            $table->timestamps();
        });

        DB::table('score_comments')->insert($this->getSeedData());
    }


    public function down(): void{
        Schema::dropIfExists('score_comments');
    }

    private function getSeedData(): array{
        $seedData = [];

        $commentsA = [
            'Incredible work! You are truly excelling.',
            'Outstanding job—your diligence shows!',
            'You’ve mastered this material brilliantly!',
            'Phenomenal! Keep pushing your limits.',
            'Fantastic performance—top marks!',
            'Exceptional results—you’re unstoppable!',
            'You have exceeded expectations; well done!',
            'Remarkable achievement—congrats!',
            'Brilliant work—an inspiration to others!',
            'You’re hitting the highest standard—amazing!'
        ];
        
        foreach ($commentsA as $comment) {
            $seedData[] = [
                'min_score' => 86,
                'max_score' => 100,
                'comment'   => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // B range (78-85)
        $commentsB = [
            'Great job! A little more effort to hit the top.',
            'Solid effort—very close to excellence.',
            'You’re doing well—aim for even higher next time.',
            'Impressive performance—keep climbing!',
            'Steady progress—maintain the momentum!',
            'Well done! You are nearly at the top band.',
            'You clearly understand most concepts.',
            'Great work—just a step away from greatness!',
            'Very good—take the extra leap to reach excellence!',
            'You should be proud—strong achievement so far!'
        ];
        foreach ($commentsB as $comment) {
            $seedData[] = [
                'min_score' => 78,
                'max_score' => 85,
                'comment'   => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // C range (60-77)
        $commentsC = [
            'Good effort—focus on refining your skills.',
            'You’re on the right track; keep studying!',
            'Solid grasp of the basics—build on that.',
            'Not bad! Strengthen areas you find weaker.',
            'Room for improvement—stay motivated!',
            'Steady effort; deepen your understanding further.',
            'You’ve come a good way—push for the next level.',
            'Encouraging result—keep practicing regularly!',
            'Fair performance; concentrate on key problem areas.',
            'Keep going! You have potential to climb higher.'
        ];
        foreach ($commentsC as $comment) {
            $seedData[] = [
                'min_score' => 60,
                'max_score' => 77,
                'comment'   => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // D range (40-59)
        $commentsD = [
            'Needs improvement—study more thoroughly.',
            'A fair try—seek extra help where needed.',
            'You can do better—review the fundamentals.',
            'Keep practicing—the basics need strengthening.',
            'Focus and consistent effort will yield progress.',
            'Stay positive—address your weaknesses systematically.',
            'Encouraging effort, but more work is required.',
            'Revise regularly to boost your comprehension.',
            'Identify key trouble spots and tackle them.',
            'A step in the right direction—keep persevering.'
        ];
        foreach ($commentsD as $comment) {
            $seedData[] = [
                'min_score' => 40,
                'max_score' => 59,
                'comment'   => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // E range (26-39)
        $commentsE = [
            'Significant effort needed to improve.',
            'Keep trying—seek help from peers or teachers.',
            'Practice consistently to raise your performance.',
            'Needs greater dedication—don’t give up.',
            'Stay committed—your performance can still improve.',
            'Challenge yourself—strengthen basic concepts.',
            'Work on foundational knowledge to move forward.',
            'Push harder—you can surpass this level.',
            'Look for resources and practice daily.',
            'Keep aiming higher—you can do it with persistence.'
        ];
        foreach ($commentsE as $comment) {
            $seedData[] = [
                'min_score' => 26,
                'max_score' => 39,
                'comment'   => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // U range (0-25)
        $commentsU = [
            'Needs a fresh start—evaluate your study approach.',
            'Identify major gaps—seek immediate help.',
            'Don’t lose hope—commit to daily study.',
            'Consider tutoring or extra guidance immediately.',
            'Focus on building a strong foundation from scratch.',
            'Every expert was once a beginner—start practicing!',
            'A low mark, but you can only go up from here.',
            'Determine the difficulties and tackle them steadily.',
            'Consult teachers/mentors—improve your technique.',
            'Remember: perseverance and practice lead to success.'
        ];
        foreach ($commentsU as $comment) {
            $seedData[] = [
                'min_score' => 0,
                'max_score' => 25,
                'comment'   => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $seedData;
    }
};
