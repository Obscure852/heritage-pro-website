<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Grade;
use App\Models\Copy;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BooksImport implements ToModel, WithHeadingRow,WithEvents, SkipsOnFailure{
    use SkipsErrors, SkipsFailures;

    private $successCount = 0;
    private $failureCount = 0;

    public function model(array $row){
        Log::info('Processing row: ', $row);

        try {
            $data = $this->sanitize($row);

            $authorName = explode(' ', $data['author'], 2);
            $firstName = $authorName[0];
            $lastName = isset($authorName[1]) ? $authorName[1] : '';

            $author = Author::firstOrCreate([
                'first_name' => $firstName,
                'last_name' => $lastName
            ]);

            $publisher = Publisher::firstOrCreate([
                'name' => $data['publisher']
            ]);

            $grade = Grade::where('name', $data['grade'])->first();
            if (!$grade) {
                throw new \Exception("Grade not found: " . $data['grade']);
            }

            $book = Book::create([
                'isbn' => $data['isbn'],
                'title' => $data['title'],
                'author_id' => $author->id,
                'grade_id' => $grade->id,
                'publication_year' => $data['publication_year'],
                'publisher_id' => $publisher->id,
                'genre' => $data['genre'],
                'language' => $data['language'],
                'format' => $data['format'],
                'quantity' => $data['quantity'] ?? 1,
                'status' => $data['status'] ?? 'available',
                'location' => $data['location'],
                'price' => $data['price'],
                'dewey_decimal' => $data['dewey_decimal'],
            ]);

            $this->createCopies($book, $data['quantity'] ?? 1);
            $this->successCount++;
            Log::info('Book imported successfully: ', $data);
            return $book;

        } catch (Throwable $e) {
            Log::error('Error processing row: ', ['error' => $e->getMessage(), 'row' => $row]);
            $this->failureCount++;
        }
    }

    protected function createCopies(Book $book, int $quantity){
        for ($i = 0; $i < $quantity; $i++) {
            Copy::create([
                'book_id' => $book->id,
                'accession_number' => $this->generateAccessionNumber(),
                'status' => 'available',
            ]);
        }
        Log::info("Created {$quantity} copies for book: {$book->title}");
    }

    protected function generateAccessionNumber(){
        return Str::upper(Str::random(8));
    }

    protected function sanitize(array $data){
        return [
            'title' => trim($data['title']),
            'isbn' => trim($data['isbn']),
            'author' => trim($data['author']),
            'grade' => trim($data['grade']),
            'publication_year' => trim($data['publication_year']),
            'publisher' => trim($data['publisher']),
            'genre' => trim($data['genre']),
            'language' => trim($data['language']),
            'format' => trim($data['format']),
            'quantity' => (int) ($data['quantity'] ?? 1),
            'price' => (float) $data['price'],
            'dewey_decimal' => trim($data['dewey_decimal']),
            'status' => trim($data['status']),
            'location' => trim($data['location']),
        ];
    }

    public function registerEvents(): array{
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $this->successCount = 0;
                $this->failureCount = 0;
                Log::info('Starting import...');
            },
            AfterImport::class => function (AfterImport $event) {
                Log::info('Import completed. Success: ' . $this->successCount . ', Failures: ' . $this->failureCount);
            },
            ImportFailed::class => function (ImportFailed $event) {
                Log::error('Import failed: ', ['error' => $event->getException()->getMessage()]);
            },
        ];
    }

    public function getSuccessCount(): int{
        return $this->successCount;
    }

    public function getFailureCount(): int{
        return $this->failureCount;
    }
}