{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
@extends('layouts.users')
@section('title', 'Edit Role')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles &amp; Permissions</a></li>
        <li class="breadcrumb-item active text-capitalize">{{ $role->role_name }}</li>
    </ol>
</nav>

<div class="card shadow-sm border-0" style="max-width: 760px">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold text-primary"><i class="bi bi-shield-lock me-1"></i> Edit role</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $role) }}" class="needs-validation" novalidate>
            @csrf @method('PUT')
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label for="role_name" class="form-label">Role name <span class="text-danger">*</span></label>
                    <input type="text" name="role_name" id="role_name" required minlength="3" maxlength="50" pattern="[a-z_]{3,50}"
                           value="{{ old('role_name', $role->role_name) }}" @readonly(\App\Support\Block1\Access::isProtectedRole($role))
                           class="form-control @error('role_name') is-invalid @enderror">
                    @if(\App\Support\Block1\Access::isProtectedRole($role))<div class="form-text">System role: the name cannot be changed.</div>@endif
                    <div class="invalid-feedback">@error('role_name'){{ $message }}@else 3-50 lowercase letters or underscores.@enderror</div>
                </div>
                <div class="col-md-7">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" name="description" id="description" maxlength="255" value="{{ old('description', $role->description) }}"
                           class="form-control @error('description') is-invalid @enderror">
                    <div class="invalid-feedback">@error('description'){{ $message }}@else Maximum 255 characters.@enderror</div>
                </div>
            </div>

            <h6 class="fw-bold">Permissions</h6>
            @include('roles._permissions', ['checked' => old('permissions', $permissions), 'prefix' => 'edit'])

            <div class="d-flex gap-2 justify-content-end mt-3">
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('form.needs-validation').on('submit', function (e) {
        if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
        $(this).addClass('was-validated');
    });
</script>
@endpush
