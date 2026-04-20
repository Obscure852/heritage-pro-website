<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLibrarySettingsRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library-settings');
    }

    public function rules(): array {
        return [
            // Borrowing rules (per borrower type)
            'loan_period_student' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'loan_period_staff' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'max_books_student' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'max_books_staff' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'max_renewals_student' => ['sometimes', 'integer', 'min:0', 'max:10'],
            'max_renewals_staff' => ['sometimes', 'integer', 'min:0', 'max:10'],

            // Currency
            'library_currency' => ['sometimes', 'string', 'max:10'],

            // Fine settings
            'fine_rate_student' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'fine_rate_staff' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'fine_threshold' => ['sometimes', 'numeric', 'min:0', 'max:10000'],
            'lost_book_fine_amount' => ['sometimes', 'numeric', 'min:0', 'max:10000'],
            'lost_book_period_student' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'lost_book_period_staff' => ['sometimes', 'integer', 'min:1', 'max:365'],

            // API Keys
            'isbndb_api_key' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Catalog settings
            'new_arrivals_period' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],

            // Catalog options - simple lists (nullable allows sentinel empty string)
            'catalog_locations'        => ['sometimes', 'nullable'],
            'catalog_locations.*'      => ['string', 'max:100'],
            'catalog_categories'       => ['sometimes', 'nullable'],
            'catalog_categories.*'     => ['string', 'max:100'],
            'catalog_reading_levels'   => ['sometimes', 'nullable'],
            'catalog_reading_levels.*' => ['string', 'max:100'],

            // Catalog options - item types with rules
            'item_types'                        => ['sometimes', 'nullable', 'array'],
            'item_types.*.name'                 => ['required_with:item_types', 'string', 'max:100'],
            'item_types.*.loan_period_student'  => ['nullable', 'integer', 'min:1', 'max:365'],
            'item_types.*.loan_period_staff'    => ['nullable', 'integer', 'min:1', 'max:365'],
            'item_types.*.fine_rate_student'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_types.*.fine_rate_staff'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_types.*.max_renewals_student' => ['nullable', 'integer', 'min:0', 'max:10'],
            'item_types.*.max_renewals_staff'   => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }

    public function messages(): array {
        return [
            'loan_period_student.min' => 'Student loan period must be at least 1 day.',
            'loan_period_staff.min' => 'Staff loan period must be at least 1 day.',
            'max_books_student.min' => 'Students must be allowed at least 1 book.',
            'max_books_staff.min' => 'Staff must be allowed at least 1 book.',
            'fine_rate_student.min' => 'Fine rate cannot be negative.',
            'fine_rate_staff.min' => 'Fine rate cannot be negative.',
            'fine_threshold.min' => 'Fine threshold cannot be negative.',
        ];
    }
}
