<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating / renaming an exam type (manager only).
 */
class ExamTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // protected by the `role:manager` middleware
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('type_name')) {
            $this->merge(['type_name' => trim(strip_tags((string) $this->input('type_name')))]);
        }
    }

    public function rules(): array
    {
        $current = $this->route('examType');

        return [
            'type_name' => [
                'required', 'string', 'min:2', 'max:50',
                Rule::unique('exam_types', 'type_name')->ignore($current?->exam_type_id, 'exam_type_id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type_name.required' => 'The exam type name is required.',
            'type_name.unique'   => 'This exam type already exists.',
        ];
    }
}
