<?php

use App\Models\SchoolSetup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

return new class extends Migration{
    public function up(){
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 13)->unique();
            $table->string('title', 255);
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_id')->constrained()->onDelete('cascade');
            $table->year('publication_year')->nullable();
            $table->foreignId('publisher_id')->constrained()->onDelete('cascade');
            $table->string('edition', 50)->nullable();
            $table->string('genre', 50)->nullable();
            $table->string('filter', 50)->nullable();
            $table->string('call_number', 50)->nullable();
            $table->string('language', 50)->nullable();
            $table->string('format', 50)->nullable();
            $table->integer('pages')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->integer('quantity')->unsigned()->default(1);
            $table->enum('status', ['available', 'checked_out', 'on_hold', 'in_repair'])->default('available');
            $table->string('location', 100)->nullable();
            $table->date('date_added')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('currency', 3)->default('BWP');
            $table->string('barcode', 50)->nullable();
            $table->string('dewey_decimal', 20)->nullable();
            $table->string('series_name', 100)->nullable();
            $table->integer('volume_number')->unsigned()->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('reading_level', 50)->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('good');
            $table->timestamps();
        
            $table->index('isbn');
            $table->index('title');
            $table->index(['grade_id', 'status']);
            $table->index('dewey_decimal');
        });
        
        Schema::create('copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('accession_number', 8)->unique();
            $table->enum('status', ['available', 'checked_out', 'in_repair', 'lost'])->default('available');
            $table->timestamps();
            
            $table->index('accession_number');
            $table->index(['book_id', 'status']);
        });

        $seniorBooks = [
            [
                'title' => 'Mathematics for Form 4',
                'isbn' => '9781118531624',
                'author_name' => 'John Doe',
                'grade_name' => 'F4',
                'publication_year' => '2015',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Mathematics',
                'price' => 450,
                'dewey_decimal' => '510',
            ],
            [
                'title' => 'Science Explorer: Earth Science',
                'isbn' => '9780132012748',
                'author_name' => 'Michael J. Padilla',
                'grade_name' => 'F4',
                'publication_year' => '2012',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Science',
                'price' => 400,
                'dewey_decimal' => '550',
            ],
            [
                'title' => 'English Literature for Form 4',
                'isbn' => '9780198320221',
                'author_name' => 'William Shakespeare',
                'grade_name' => 'F4',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Literature',
                'price' => 320,
                'dewey_decimal' => '820',
            ],
            [
                'title' => 'Social Studies: History Alive!',
                'isbn' => '9781583713514',
                'author_name' => 'Bert Bower',
                'grade_name' => 'F4',
                'publication_year' => '2016',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'History',
                'price' => 500,
                'dewey_decimal' => '900',
            ],
            [
                'title' => 'Physical Science: Concepts in Action',
                'isbn' => '9780131663088',
                'author_name' => 'Frank Dinah',
                'grade_name' => 'F4',
                'publication_year' => '2017',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Science',
                'price' => 350,
                'dewey_decimal' => '530',
            ],
            [
                'title' => 'Geography for Form 4',
                'isbn' => '9781408523172',
                'author_name' => 'Alice Paul',
                'grade_name' => 'F4',
                'publication_year' => '2014',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Geography',
                'price' => 420,
                'dewey_decimal' => '910',
            ],
            [
                'title' => 'Algebra 1: Common Core',
                'isbn' => '9780133185489',
                'author_name' => 'Charles, Randall I.',
                'grade_name' => 'F4',
                'publication_year' => '2013',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Mathematics',
                'price' => 480,
                'dewey_decimal' => '512',
            ],
            [
                'title' => 'Life Science: Biology Basics',
                'isbn' => '9780030735364',
                'author_name' => 'Kenneth Miller',
                'grade_name' => 'F4',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Biology',
                'price' => 370,
                'dewey_decimal' => '570',
            ],
            [
                'title' => 'Modern World History for Form 4',
                'isbn' => '9781107613878',
                'author_name' => 'Ben Walsh',
                'grade_name' => 'F4',
                'publication_year' => '2020',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'History',
                'price' => 430,
                'dewey_decimal' => '909',
            ],
            [
                'title' => 'Chemistry: The Central Science',
                'isbn' => '9780136006170',
                'author_name' => 'Theodore Brown',
                'grade_name' => 'F4',
                'publication_year' => '2011',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Chemistry',
                'price' => 460,
                'dewey_decimal' => '540',
            ],
            [
                'title' => 'Exploring Music Theory',
                'isbn' => '9780825833488',
                'author_name' => 'Robert W. Ottman',
                'grade_name' => 'F4',
                'publication_year' => '2010',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Music',
                'price' => 300,
                'dewey_decimal' => '780',
            ],
            [
                'title' => 'Introduction to Physical Education',
                'isbn' => '9780073522777',
                'author_name' => 'Robert P. Pangrazi',
                'grade_name' => 'F5',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Physical Education',
                'price' => 350,
                'dewey_decimal' => '796',
            ],
            [
                'title' => 'Environmental Science for Form 5',
                'isbn' => '9781118977920',
                'author_name' => 'Richard T. Wright',
                'grade_name' => 'F5',
                'publication_year' => '2016',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Environmental Science',
                'price' => 370,
                'dewey_decimal' => '363.7',
            ],
            [
                'title' => 'English Grammar and Composition',
                'isbn' => '9780673600587',
                'author_name' => 'John E. Warriner',
                'grade_name' => 'F5',
                'publication_year' => '2014',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'English',
                'price' => 300,
                'dewey_decimal' => '425',
            ],
            [
                'title' => 'Principles of Computer Science',
                'isbn' => '9780131856039',
                'author_name' => 'George Beekman',
                'grade_name' => 'F5',
                'publication_year' => '2015',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Computer Science',
                'price' => 410,
                'dewey_decimal' => '004',
            ],
            [
                'title' => 'Creative Writing Workshop',
                'isbn' => '9780072874679',
                'author_name' => 'Jane Piirto',
                'grade_name' => 'F5',
                'publication_year' => '2013',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Writing',
                'price' => 290,
                'dewey_decimal' => '808',
            ],
            [
                'title' => 'Art Appreciation for Young Learners',
                'isbn' => '9780205172917',
                'author_name' => 'Gene A. Mittler',
                'grade_name' => 'F5',
                'publication_year' => '2013',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Art',
                'price' => 280,
                'dewey_decimal' => '700',
            ],
            [
                'title' => 'Introduction to Health and Wellness',
                'isbn' => '9781259239542',
                'author_name' => 'Gordon Edlin',
                'grade_name' => 'F5',
                'publication_year' => '2016',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Health',
                'price' => 320,
                'dewey_decimal' => '613',
            ],
            [
                'title' => "Basic Economics: A Citizen's Guide to the Economy",
                'isbn' => '9780465022526',
                'author_name' => 'Thomas Sowell',
                'grade_name' => 'F5',
                'publication_year' => '2011',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Economics',
                'price' => 340,
                'dewey_decimal' => '330',
            ],
            [
                'title' => 'Introduction to Literature',
                'isbn' => '9780134099143',
                'author_name' => 'Sylvan Barnet',
                'grade_name' => 'F5',
                'publication_year' => '2015',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Literature',
                'price' => 360,
                'dewey_decimal' => '808',
            ],
        ];



        $juniorBooks = [
            [
                'title' => 'Junior Mathematics Foundation',
                'isbn' => '9780134567891',
                'author_name' => 'David Thompson',
                'grade_name' => 'F1',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Mathematics',
                'price' => 280,
                'dewey_decimal' => '510',
            ],
            [
                'title' => 'Integrated Science: First Steps',
                'isbn' => '9780134567892',
                'author_name' => 'Sarah Johnson',
                'grade_name' => 'F1',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Science',
                'price' => 300,
                'dewey_decimal' => '500',
            ],
            [
                'title' => 'English Grammar Fundamentals',
                'isbn' => '9780134567893',
                'author_name' => 'Michael Brown',
                'grade_name' => 'F1',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'English',
                'price' => 250,
                'dewey_decimal' => '425',
            ],
            [
                'title' => 'World Geography: Our Earth',
                'isbn' => '9780134567894',
                'author_name' => 'Robert Clark',
                'grade_name' => 'F1',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Geography',
                'price' => 270,
                'dewey_decimal' => '910',
            ],
            [
                'title' => 'Introduction to Agriculture',
                'isbn' => '9780134567895',
                'author_name' => 'Peter Wilson',
                'grade_name' => 'F1',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Agriculture',
                'price' => 260,
                'dewey_decimal' => '630',
            ],
            [
                'title' => 'Basic French Language',
                'isbn' => '9780134567896',
                'author_name' => 'Marie Laurent',
                'grade_name' => 'F1',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Languages',
                'price' => 240,
                'dewey_decimal' => '440',
            ],
            [
                'title' => 'Religious Education: World Faiths',
                'isbn' => '9780134567897',
                'author_name' => 'James White',
                'grade_name' => 'F1',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Religious Studies',
                'price' => 230,
                'dewey_decimal' => '200',
            ],
            [
                'title' => 'Introduction to Design & Technology',
                'isbn' => '9780134567898',
                'author_name' => 'Thomas Anderson',
                'grade_name' => 'F1',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Technology',
                'price' => 290,
                'dewey_decimal' => '600',
            ],
            [
                'title' => 'Music Theory Basics',
                'isbn' => '9780134567899',
                'author_name' => 'Richard Moore',
                'grade_name' => 'F1',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Music',
                'price' => 220,
                'dewey_decimal' => '780',
            ],
            [
                'title' => 'Physical Education & Health',
                'isbn' => '9780134567900',
                'author_name' => 'Susan Taylor',
                'grade_name' => 'F1',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Physical Education',
                'price' => 210,
                'dewey_decimal' => '613',
            ],

            [
                'title' => 'Intermediate Mathematics',
                'isbn' => '9780134567901',
                'author_name' => 'John Peterson',
                'grade_name' => 'F2',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Mathematics',
                'price' => 290,
                'dewey_decimal' => '510',
            ],
            [
                'title' => 'Science: The Natural World',
                'isbn' => '9780134567902',
                'author_name' => 'Emily Roberts',
                'grade_name' => 'F2',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Science',
                'price' => 310,
                'dewey_decimal' => '500',
            ],
            [
                'title' => 'Advanced English Language',
                'isbn' => '9780134567903',
                'author_name' => 'Patricia Harris',
                'grade_name' => 'F2',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'English',
                'price' => 270,
                'dewey_decimal' => '428',
            ],
            [
                'title' => 'Social Studies: Our World',
                'isbn' => '9780134567904',
                'author_name' => 'William Turner',
                'grade_name' => 'F2',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Social Studies',
                'price' => 280,
                'dewey_decimal' => '300',
            ],
            [
                'title' => 'Agricultural Sciences',
                'isbn' => '9780134567905',
                'author_name' => 'George Martin',
                'grade_name' => 'F2',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Agriculture',
                'price' => 270,
                'dewey_decimal' => '630',
            ],
            [
                'title' => 'French Language & Culture',
                'isbn' => '9780134567906',
                'author_name' => 'Claire Dubois',
                'grade_name' => 'F2',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Languages',
                'price' => 250,
                'dewey_decimal' => '440',
            ],
            [
                'title' => 'Religious Studies & Ethics',
                'isbn' => '9780134567907',
                'author_name' => 'David Wilson',
                'grade_name' => 'F2',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Religious Studies',
                'price' => 240,
                'dewey_decimal' => '200',
            ],
            [
                'title' => 'Design & Technology Projects',
                'isbn' => '9780134567908',
                'author_name' => 'Mark Stevens',
                'grade_name' => 'F2',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Technology',
                'price' => 300,
                'dewey_decimal' => '600',
            ],
            [
                'title' => 'Music Appreciation',
                'isbn' => '9780134567909',
                'author_name' => 'Lisa Johnson',
                'grade_name' => 'F2',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Music',
                'price' => 230,
                'dewey_decimal' => '780',
            ],
            [
                'title' => 'Health & Physical Fitness',
                'isbn' => '9780134567910',
                'author_name' => 'Michael Scott',
                'grade_name' => 'F2',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Physical Education',
                'price' => 220,
                'dewey_decimal' => '613',
            ],

            [
                'title' => 'Advanced Mathematics Concepts',
                'isbn' => '9780134567911',
                'author_name' => 'Robert Williams',
                'grade_name' => 'F3',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Mathematics',
                'price' => 320,
                'dewey_decimal' => '510',
            ],
            [
                'title' => 'Comprehensive Science',
                'isbn' => '9780134567912',
                'author_name' => 'Jennifer Adams',
                'grade_name' => 'F3',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Science',
                'price' => 330,
                'dewey_decimal' => '500',
            ],
            [
                'title' => 'English Literature & Composition',
                'isbn' => '9780134567913',
                'author_name' => 'Charles Miller',
                'grade_name' => 'F3',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'English',
                'price' => 290,
                'dewey_decimal' => '820',
            ],
            [
                'title' => 'World History & Geography',
                'isbn' => '9780134567914',
                'author_name' => 'Elizabeth Davis',
                'grade_name' => 'F3',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Social Studies',
                'price' => 310,
                'dewey_decimal' => '909',
            ],
            [
                'title' => 'Advanced Agricultural Practice',
                'isbn' => '9780134567915',
                'author_name' => 'Andrew Wilson',
                'grade_name' => 'F3',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Agriculture',
                'price' => 280,
                'dewey_decimal' => '630',
            ],
            [
                'title' => 'French Advanced Studies',
                'isbn' => '9780134567916',
                'author_name' => 'Pierre Dubois',
                'grade_name' => 'F3',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Languages',
                'price' => 270,
                'dewey_decimal' => '440',
            ],
            [
                'title' => 'Comparative Religious Studies',
                'isbn' => '9780134567917',
                'author_name' => 'Benjamin Green',
                'grade_name' => 'F3',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Religious Studies',
                'price' => 260,
                'dewey_decimal' => '200',
            ],
            [
                'title' => 'Advanced Design & Technology',
                'isbn' => '9780134567918',
                'author_name' => 'Christopher Lee',
                'grade_name' => 'F3',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Hardcover',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Technology',
                'price' => 310,
                'dewey_decimal' => '600',
            ],
            [
                'title' => 'Advanced Music Theory & Practice',
                'isbn' => '9780134567919',
                'author_name' => 'Rebecca Morgan',
                'grade_name' => 'F3',
                'publication_year' => '2018',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Music',
                'price' => 250,
                'dewey_decimal' => '780',
            ],
            [
                'title' => 'Sports Science & Physical Education',
                'isbn' => '9780134567920',
                'author_name' => 'Daniel Cooper',
                'grade_name' => 'F3',
                'publication_year' => '2019',
                'genre' => 'Textbook',
                'language' => 'English',
                'format' => 'Paperback',
                'quantity' => 10,
                'status' => 'available',
                'location' => 'Physical Education',
                'price' => 240,
                'dewey_decimal' => '613',
            ]
        ];

        try {
            DB::transaction(function() use ($juniorBooks, $seniorBooks) {
                $schoolType = DB::table('school_setup')->value('type');
                $books = $schoolType === 'Junior' ? $juniorBooks : $seniorBooks;
                $validGrades = $schoolType === 'Junior' ? ['F1', 'F2', 'F3'] : ['F4', 'F5'];
                
                $grades = DB::table('grades')
                    ->whereIn('name', $validGrades)
                    ->pluck('id', 'name');
    
                $books = $this->validateAndProcessBooks($books, $grades);
                if (empty($books)) {
                    Log::warning('No valid books to import');
                    return;
                }
    
                foreach (array_chunk($books, 100) as $bookChunk) {
                    $copyInserts = [];
                    
                    foreach ($bookChunk as $book) {
                        try {
                            $authorId = $this->insertOrGetAuthor($book['author_name']);
                            
                            $bookId = DB::table('books')->insertGetId([
                                'isbn' => $book['isbn'],
                                'title' => $book['title'],
                                'author_id' => $authorId,
                                'grade_id' => $grades[$book['grade_name']],
                                'publication_year' => $book['publication_year'],
                                'publisher_id' => $this->insertOrGetPublisher('Pearson'),
                                'genre' => $book['genre'],
                                'language' => $book['language'],
                                'format' => $book['format'],
                                'quantity' => $book['quantity'],
                                'status' => strtolower($book['status']),
                                'location' => $book['location'],
                                'price' => $book['price'],
                                'dewey_decimal' => $book['dewey_decimal'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
    
                            for ($i = 0; $i < $book['quantity']; $i++) {
                                $copyInserts[] = [
                                    'book_id' => $bookId,
                                    'accession_number' => $this->generateUniqueAccessionNumber(),
                                    'status' => 'available',
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                            
                        } catch (\Exception $e) {
                            Log::error('Error processing book', [
                                'book' => $book['title'],
                                'error' => $e->getMessage()
                            ]);
                            continue;
                        }
                    }
    
                    foreach (array_chunk($copyInserts, 1000) as $copies) {
                        try {
                            DB::table('copies')->insert($copies);
                        } catch (\Exception $e) {
                            Log::error('Error inserting copies', ['error' => $e->getMessage()]);
                            continue;
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Migration failed', ['error' => $e->getMessage()]);
            return true;
        }
    }

    private function validateAndProcessBooks($books, $grades) {
        $validBooks = [];
        foreach ($books as $book) {
            if (!isset($grades[$book['grade_name']])) continue;
            if (!preg_match('/^[\d-]{13}$/', $book['isbn'])) continue;
            if ($book['quantity'] < 1) continue;
            $validBooks[] = $book;
        }
        return $validBooks;
    }
    
    private function insertOrGetAuthor($authorName) {
        $nameParts = explode(' ', $authorName, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        
        $author = DB::table('authors')
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->first();
            
        if (!$author) {
            return DB::table('authors')->insertGetId([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        return $author->id;
    }

    private function insertOrGetPublisher($publisherName) {
        $publisher = DB::table('publishers')
            ->where('name', $publisherName)
            ->first();
            
        if (!$publisher) {
            return DB::table('publishers')->insertGetId([
                'name' => $publisherName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        return $publisher->id;
    }
    
    private function generateUniqueAccessionNumber() {
        do {
            $number = Str::random(8);
        } while (DB::table('copies')->where('accession_number', $number)->exists());
        return $number;
    }

    public function down(){
        Schema::dropIfExists('books');
        Schema::dropIfExists('copies');
    }

};
