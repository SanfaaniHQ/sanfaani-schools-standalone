<?php

namespace App\Http\Requests\School;

use App\Models\Term;
use App\Services\CurrentSchoolService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreScratchCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $school = app(CurrentSchoolService::class)->get($this->user());
        $schoolId = $school?->id ?: 0;

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'school_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')->where('school_id', $schoolId),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $schoolId),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')->where('school_id', $schoolId),
            ],
            'result_type' => ['required', Rule::in(['term_result'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:2000'],
            'generation_mode' => ['nullable', Rule::in(['direct', 'request'])],
            'max_uses' => ['required_if:generation_mode,direct', 'nullable', 'integer', 'min:1', 'max:100'],
            'payment_method' => ['nullable', Rule::in(['bank_transfer', 'cash', 'manual'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'request_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $school = app(CurrentSchoolService::class)->get($this->user());

            if (! $school) {
                $validator->errors()->add('school_id', 'Your account is not assigned to a school.');

                return;
            }

            $termMatchesSession = Term::where('id', $this->input('term_id'))
                ->where('school_id', $school->id)
                ->where('academic_session_id', $this->input('academic_session_id'))
                ->exists();

            if (! $termMatchesSession) {
                $validator->errors()->add('term_id', 'The selected term does not belong to the selected academic session.');
            }
        });
    }
}
