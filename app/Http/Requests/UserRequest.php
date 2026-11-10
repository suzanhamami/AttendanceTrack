<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Requests;

use App\Models\Role;
use App\Support\Block1\Access;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

/**
 * Server-side validation for adding and editing a user (spec: Validations - Block 1 + general rules).
 * The same rules are mirrored in the form with HTML5 attributes + JavaScript.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware already checks users.create / users.edit; this is the controller-level check.
        return Access::has($this->user(), $this->isMethod('post') ? 'users.create' : 'users.edit');
    }

    /** Trim / clean text inputs before validating (sanitization). */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim(strip_tags((string) $this->input('full_name'))),
            'username'  => trim((string) $this->input('username')),
            'email'     => strtolower(trim((string) $this->input('email'))),
            'phone'     => trim((string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');           // null when adding
        $ignoreId = $user?->user_id;
        $actor = $this->user();

        return [
            // letters (any language), spaces, dots, hyphens and apostrophes - no digits
            'full_name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\s\.\'\-]+$/u'],
            'username'  => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9_.]+$/',
                            Rule::unique('users', 'username')->ignore($ignoreId, 'user_id')],
            'email'     => ['required', 'string', 'email', 'max:100',
                            Rule::unique('users', 'email')->ignore($ignoreId, 'user_id')],
            'password'  => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'phone'     => ['required', 'digits:10'],
            'role_id'   => ['required', 'integer', Rule::exists('roles', 'role_id'),
                            // Only privileged users (admin / manager) may give a privileged role.
                            function (string $attribute, mixed $value, Closure $fail) use ($actor) {
                                if (Access::roleIsPrivileged((int) $value) && !Access::isPrivileged($actor)) {
                                    $fail('You are not allowed to assign this role.');
                                }
                            }],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'department_id')],
            'birth_date'    => ['required', 'date', 'after:1900-01-01', 'before_or_equal:' . now()->subYears(18)->toDateString(),
                                // General rule of the spec: instructors are between 18 and 60 years old.
                                function (string $attribute, mixed $value, Closure $fail) {
                                    $role = Role::find($this->input('role_id'));
                                    if ($role && $role->role_name === 'instructor' && strtotime((string) $value)
                                        && Carbon::parse($value)->lt(now()->subYears(61)->addDay())) {
                                        $fail('An instructor must be between 18 and 60 years old.');
                                    }
                                }],
            'photo'         => ['nullable', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024), 'image'],
            // Status (active / inactive) is set by the manager only; for others the field is ignored.
            'status'        => Access::has($actor, 'users.status') ? ['required', Rule::in(['active', 'inactive'])] : ['exclude'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.regex'            => 'The name may only contain letters and spaces.',
            'username.regex'             => 'The username may only contain English letters, numbers, dots and underscores.',
            'phone.digits'               => 'The phone number must be exactly 10 digits.',
            'birth_date.before_or_equal' => 'The user must be at least 18 years old.',
            'password.confirmed'         => 'The password confirmation does not match.',
            'photo.max'                  => 'The photo must be smaller than 2 MB.',
            'status.in'                  => 'The status must be Active or Inactive.',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name'     => 'full name',
            'role_id'       => 'role',
            'department_id' => 'department',
            'birth_date'    => 'birth date',
        ];
    }
}
