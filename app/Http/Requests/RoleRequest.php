<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

namespace App\Http\Requests;

use App\Support\Block1\Access;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation for adding / editing a role and its permissions. */
class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Access::has($this->user(), 'roles.manage');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'role_name'   => strtolower(trim((string) $this->input('role_name'))),
            'description' => trim(strip_tags((string) $this->input('description'))) ?: null,
        ]);
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'role_name'     => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z_]+$/',
                                Rule::unique('roles', 'role_name')->ignore($role?->role_id, 'role_id')],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Access::catalog())],
        ];
    }

    public function messages(): array
    {
        return [
            'role_name.regex' => 'The role name may only contain lowercase English letters and underscores (e.g. exam_affairs).',
        ];
    }
}
