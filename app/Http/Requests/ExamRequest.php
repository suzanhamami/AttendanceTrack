<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

namespace App\Http\Requests;

use App\Models\Exam;
use App\Models\Section;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating AND updating an exam (spec section "Validations - Block 5").
 */
class ExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access control is done by the `role:` middleware on the routes.
        return true;
    }

    /** Sanitise text input before validating (XSS protection, in addition to Blade escaping). */
    protected function prepareForValidation(): void
    {
        if ($this->has('room')) {
            $this->merge(['room' => trim(strip_tags((string) $this->input('room')))]);
        }
    }

    public function rules(): array
    {
        return [
            'exam_type_id' => ['required', 'integer', 'exists:exam_types,exam_type_id'],
            'exam_date'    => ['required', 'date_format:Y-m-d'],
            'exam_time'    => ['required', 'date_format:H:i'],
            'duration'     => ['required', 'integer', 'min:1'],
            'max_grade'    => ['required', 'numeric', 'between:10,100'],
            'min_grade'    => ['required', 'numeric', 'min:0.01', 'lt:max_grade'],
            'section_id'   => ['required', 'integer', 'exists:sections,section_id'],
            'course_id'    => ['required', 'integer', 'exists:courses,course_id'],
            'room'         => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'exam_type_id.required' => 'Please choose the exam type.',
            'exam_type_id.exists'   => 'The selected exam type does not exist.',
            'exam_date.required'    => 'The exam date is required.',
            'exam_date.date_format' => 'The exam date must be a valid date (YYYY-MM-DD).',
            'exam_time.required'    => 'The exam time is required.',
            'exam_time.date_format' => 'The exam time must be a valid time (HH:MM).',
            'duration.integer'      => 'The duration must be a whole number of minutes.',
            'duration.min'          => 'The duration must be a positive number of minutes.',
            'max_grade.between'     => 'The maximum grade must be between 10 and 100.',
            'min_grade.min'         => 'The minimum grade must be a positive number.',
            'min_grade.lt'          => 'The minimum grade must be less than the maximum grade.',
            'section_id.exists'     => 'The selected section does not exist.',
            'course_id.exists'      => 'The selected course does not exist.',
            'room.required'         => 'The room is required.',
        ];
    }

    /** Cross-field and cross-table business rules. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // 1. The exam's section must belong to the exam's course.
            if (! $v->errors()->hasAny(['section_id', 'course_id'])) {
                $section = Section::find($this->input('section_id'));

                if ($section && (int) $section->course_id !== (int) $this->input('course_id')) {
                    $v->errors()->add('section_id', 'The selected section does not belong to the selected course.');
                }
            }

            // 2. When grades are already recorded, keep them consistent.
            $exam = $this->route('exam');

            if ($exam instanceof Exam && $exam->grades()->exists()) {
                if ((int) $this->input('section_id') !== (int) $exam->section_id
                    || (int) $this->input('course_id') !== (int) $exam->course_id) {
                    $v->errors()->add('section_id', 'The section / course cannot be changed because grades are already recorded for this exam.');
                }

                $highest = (float) $exam->grades()->max('grade');

                if (! $v->errors()->has('max_grade') && is_numeric($this->input('max_grade'))
                    && (float) $this->input('max_grade') < $highest) {
                    $v->errors()->add('max_grade', "The maximum grade cannot be lower than the highest recorded grade ($highest).");
                }
            }
        });
    }
}
