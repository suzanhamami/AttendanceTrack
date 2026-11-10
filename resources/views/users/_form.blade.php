{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
{{--
    Shared add / edit form. Expects $roles, $departments and optional $user (edit mode).
    Client-side validation: HTML5 attributes + the script below. The same rules run on the server (UserRequest).
--}}
@php($editing = isset($user))
@php($maxBirth = now()->subYears(18)->toDateString())

@if($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i> Please fix the highlighted fields.
    </div>
@endif

<form id="user-form" method="POST" enctype="multipart/form-data" novalidate
      action="{{ $editing ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="row g-4">
        {{-- Photo --}}
        <div class="col-lg-3 text-center">
            <div class="mb-2">
                <img id="photo-preview" src="{{ $editing && \App\Support\Block1\Access::photoUrl($user) ? \App\Support\Block1\Access::photoUrl($user) : '' }}" alt="Photo preview"
                     class="avatar-lg border {{ $editing && \App\Support\Block1\Access::photoUrl($user) ? '' : 'd-none' }}">
                <div id="photo-placeholder" class="avatar-lg avatar-initials mx-auto fs-1 {{ $editing && \App\Support\Block1\Access::photoUrl($user) ? 'd-none' : '' }}">
                    <i class="bi bi-person"></i>
                </div>
            </div>
            <label for="photo" class="form-label small text-muted">Profile photo (JPG/PNG, max 2 MB)</label>
            <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                   class="form-control form-control-sm @error('photo') is-invalid @enderror">
            <div class="invalid-feedback">@error('photo'){{ $message }}@else Please choose a JPG or PNG image smaller than 2 MB.@enderror</div>
            @if($editing && $user->photo)
                <div class="form-check mt-2 text-start">
                    <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo">
                    <label class="form-check-label small" for="remove_photo">Remove current photo</label>
                </div>
            @endif
        </div>

        <div class="col-lg-9">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" id="full_name" required minlength="3" maxlength="100"
                           pattern="[^0-9<>]{3,100}" value="{{ old('full_name', $user->full_name ?? '') }}"
                           class="form-control @error('full_name') is-invalid @enderror">
                    <div class="invalid-feedback">@error('full_name'){{ $message }}@else Name is required (3-100 letters, no digits).@enderror</div>
                </div>
                <div class="col-md-6">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" required minlength="3" maxlength="50"
                           pattern="[A-Za-z0-9_.]{3,50}" autocomplete="off" value="{{ old('username', $user->username ?? '') }}"
                           class="form-control @error('username') is-invalid @enderror">
                    <div class="invalid-feedback">@error('username'){{ $message }}@else 3-50 English letters, numbers, dots or underscores.@enderror</div>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" required maxlength="100" value="{{ old('email', $user->email ?? '') }}"
                           class="form-control @error('email') is-invalid @enderror">
                    <div class="invalid-feedback">@error('email'){{ $message }}@else Please enter a valid email address.@enderror</div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" id="phone" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric"
                           placeholder="09xxxxxxxx" value="{{ old('phone', $user->phone ?? '') }}"
                           class="form-control @error('phone') is-invalid @enderror">
                    <div class="invalid-feedback">@error('phone'){{ $message }}@else The phone number must be exactly 10 digits.@enderror</div>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password @unless($editing)<span class="text-danger">*</span>@endunless</label>
                    <input type="password" name="password" id="password" minlength="8" autocomplete="new-password" @unless($editing) required @endunless
                           class="form-control @error('password') is-invalid @enderror">
                    <div class="form-text">{{ $editing ? 'Leave empty to keep the current password. ' : '' }}At least 8 characters with a letter and a number.</div>
                    <div class="invalid-feedback">@error('password'){{ $message }}@else At least 8 characters, including a letter and a number.@enderror</div>
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm password @unless($editing)<span class="text-danger">*</span>@endunless</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" @unless($editing) required @endunless
                           class="form-control">
                    <div class="invalid-feedback">The password confirmation does not match.</div>
                </div>
                <div class="col-md-4">
                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role_id" id="role_id" required class="form-select @error('role_id') is-invalid @enderror">
                        <option value="">-- Select role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->role_id }}" @selected(old('role_id', $user->role_id ?? '') == $role->role_id)>{{ ucfirst($role->role_name) }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">@error('role_id'){{ $message }}@else Please select a role.@enderror</div>
                </div>
                <div class="col-md-4">
                    <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                    <select name="department_id" id="department_id" required class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">-- Select department --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->department_id }}" @selected(old('department_id', $user->department_id ?? '') == $department->department_id)>{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">@error('department_id'){{ $message }}@else Please select a department.@enderror</div>
                </div>
                <div class="col-md-4">
                    <label for="birth_date" class="form-label">Birth date <span class="text-danger">*</span></label>
                    <input type="date" name="birth_date" id="birth_date" required min="1900-01-02" max="{{ $maxBirth }}"
                           value="{{ old('birth_date', isset($user) ? \App\Support\Block1\Access::birthDate($user)?->toDateString() : '') }}"
                           class="form-control @error('birth_date') is-invalid @enderror">
                    <div class="invalid-feedback">@error('birth_date'){{ $message }}@else The user must be at least 18 years old.@enderror</div>
                </div>
                {{-- Account status: only the department manager may set it --}}
                @if(!empty($canStatus) && !($editing && $user->is(auth()->user())))
                <div class="col-md-4">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" required class="form-select @error('status') is-invalid @enderror">
                        <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $user->status ?? 'active') === 'inactive')>Inactive</option>
                    </select>
                    <div class="invalid-feedback">@error('status'){{ $message }}@else Please select a status.@enderror</div>
                </div>
                @endif
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
                <a href="{{ $editing ? route('users.show', $user) : route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> {{ $editing ? 'Save changes' : 'Add user' }}</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function ($) {
    var MAX_PHOTO = 2 * 1024 * 1024;
    var editing = @json($editing);

    // Extra checks HTML5 cannot express; each one sets a custom validity message.
    function validateCustom() {
        var pass = $('#password')[0], confirm = $('#password_confirmation')[0], photo = $('#photo')[0];

        var p = pass.value;
        pass.setCustomValidity(p && !(/[A-Za-z]/.test(p) && /[0-9]/.test(p)) ? 'letter+number' : '');
        confirm.setCustomValidity((p || !editing) && confirm.value !== p ? 'mismatch' : '');

        var file = photo.files[0];
        photo.setCustomValidity(file && (file.size > MAX_PHOTO || !/^image\/(jpeg|png)$/.test(file.type)) ? 'bad photo' : '');
    }

    $('#user-form').on('submit', function (e) {
        validateCustom();
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    $('#user-form').on('input change', 'input, select', validateCustom);

    // Phone: digits only.
    $('#phone').on('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 10); });

    // Photo preview.
    $('#photo').on('change', function () {
        var file = this.files[0];
        if (!file || !/^image\/(jpeg|png)$/.test(file.type)) { return; }
        var reader = new FileReader();
        reader.onload = function (ev) {
            $('#photo-preview').attr('src', ev.target.result).removeClass('d-none');
            $('#photo-placeholder').addClass('d-none');
        };
        reader.readAsDataURL(file);
    });
})(jQuery);
</script>
@endpush
