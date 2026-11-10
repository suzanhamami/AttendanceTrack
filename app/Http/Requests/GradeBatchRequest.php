<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for recording / updating the grades of one exam (grades[student_id] => value).
 */
class GradeBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // protected by the `role:` middleware
    }

    public function rules(): array
    {
        /** @var Exam $exam */
        $exam = $this->route('exam');

        return [
            'recorded_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'grades'        => ['required', 'array'],
            // Empty inputs mean "no grade yet" and are ignored; filled ones must be 0..max_grade.
            'grades.*'      => ['nullable', 'numeric', 'min:0', 'max:' . $exam->max_grade],
        ];
    }

    public function messages(): array
    {
        /** @var Exam $exam */
        $exam = $this->route('exam');

        return [
            'recorded_date.required'        => 'The recording date is required.',
            'recorded_date.date_format'     => 'The recording date must be a valid date (YYYY-MM-DD).',
            'recorded_date.before_or_equal' => 'The recording date cannot be in the future.',
            'grades.required'               => 'There are no students to grade.',
            'grades.*.numeric'              => 'A grade must be a number.',
            'grades.*.min'                  => 'A grade cannot be negative.',
            'grades.*.max'                  => "A grade cannot exceed the exam's maximum grade ({$exam->max_grade}).",
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var Exam $exam */
            $exam = $this->route('exam');
            $grades = (array) $this->input('grades', []);

            // Only students registered in the exam's section can receive a grade.
            $enrolled = $exam->enrolledStudents()->pluck('student_id')->map(fn ($id) => (int) $id)->all();

            foreach (array_keys($grades) as $studentId) {
                if (! in_array((int) $studentId, $enrolled, true)) {
                    $v->errors()->add("grades.$studentId", 'This student is not registered in the exam section.');
                }
            }

            // Saving a form with every field empty is almost certainly a mistake.
            $filled = array_filter($grades, fn ($g) => $g !== null && $g !== '');

            if (! $v->errors()->has('grades') && count($filled) === 0) {
                $v->errors()->add('grades', 'Enter at least one grade before saving.');
            }
        });
    }
}
