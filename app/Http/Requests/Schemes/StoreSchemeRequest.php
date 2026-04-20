<?php

namespace App\Http\Requests\Schemes;

use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Schemes\SchemeOfWork;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSchemeRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('create', SchemeOfWork::class);
    }

    public function rules(): array {
        return [
            'subject'     => ['required', 'string', 'regex:/^(class|optional)_\d+$/'],
            'term_id'     => ['required', 'integer', 'exists:terms,id'],
            'total_weeks' => ['sometimes', 'integer', 'min:1', 'max:52'],
        ];
    }

    public function withValidator(Validator $validator): void {
        $validator->after(function (Validator $v) {
            $subject = $this->input('subject');

            if (!$subject || !preg_match('/^(class|optional)_(\d+)$/', $subject, $matches)) {
                return;
            }

            [$full, $type, $id] = $matches;

            if ($type === 'class') {
                $exists = KlassSubject::where('id', $id)
                    ->where('user_id', auth()->id())
                    ->exists();

                if (!$exists) {
                    $v->errors()->add('subject', 'The selected class subject is invalid or does not belong to you.');
                    return;
                }

                // Check for existing non-deleted scheme for this class+term
                $duplicate = SchemeOfWork::where('klass_subject_id', $id)
                    ->where('term_id', $this->input('term_id'))
                    ->exists();

                if ($duplicate) {
                    $v->errors()->add('subject', 'A scheme of work already exists for this class subject in the selected term.');
                }
            } else {
                $exists = OptionalSubject::where('id', $id)
                    ->where('user_id', auth()->id())
                    ->exists();

                if (!$exists) {
                    $v->errors()->add('subject', 'The selected optional subject is invalid or does not belong to you.');
                    return;
                }

                // Check for existing non-deleted scheme for this optional+term
                $duplicate = SchemeOfWork::where('optional_subject_id', $id)
                    ->where('term_id', $this->input('term_id'))
                    ->exists();

                if ($duplicate) {
                    $v->errors()->add('subject', 'A scheme of work already exists for this optional subject in the selected term.');
                }
            }
        });
    }

    /**
     * Transform the composite "subject" field back into the
     * klass_subject_id / optional_subject_id shape that
     * SchemeService::createWithEntries() expects.
     */
    public function validated($key = null, $default = null): mixed {
        $data = parent::validated($key, $default);

        if ($key !== null) {
            return $data;
        }

        preg_match('/^(class|optional)_(\d+)$/', $data['subject'], $matches);

        $data['klass_subject_id']    = $matches[1] === 'class' ? (int) $matches[2] : null;
        $data['optional_subject_id'] = $matches[1] === 'optional' ? (int) $matches[2] : null;

        unset($data['subject']);

        return $data;
    }
}
