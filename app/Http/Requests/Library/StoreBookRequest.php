<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'isbn'            => ['required', 'string', 'max:17', 'unique:books,isbn'],
            'title'           => ['required', 'string', 'max:500'],
            'author_names'    => ['nullable', 'string', 'max:1000'],
            'publisher_name'  => ['nullable', 'string', 'max:255'],
            'grade_id'        => ['nullable', 'exists:grades,id'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:' . (date('Y') + 1)],
            'edition'         => ['nullable', 'string', 'max:50'],
            'genre'           => ['nullable', 'string', 'max:100'],
            'language'        => ['nullable', 'string', 'max:50'],
            'format'          => ['nullable', 'string', 'max:50'],
            'pages'           => ['nullable', 'integer', 'min:1'],
            'description'     => ['nullable', 'string', 'max:5000'],
            'cover_image_url' => ['nullable', 'url', 'max:1000'],
            'dewey_decimal'   => ['nullable', 'string', 'max:20'],
            'reading_level'   => ['nullable', 'string', 'max:50'],
            'condition'       => ['nullable', 'string', 'max:50'],
            'keywords'        => ['nullable', 'string', 'max:500'],
            'price'           => ['nullable', 'numeric', 'min:0'],
            'currency'        => ['nullable', 'string', 'max:10'],
            'location'        => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array {
        return [
            'isbn.required'  => 'ISBN is required.',
            'isbn.unique'    => 'A book with this ISBN already exists in the catalog.',
            'title.required' => 'Book title is required.',
            'publication_year.min' => 'Publication year must be a valid year.',
            'publication_year.max' => 'Publication year cannot be in the future.',
            'pages.min'      => 'Page count must be at least 1.',
            'price.min'      => 'Price cannot be negative.',
        ];
    }
}
