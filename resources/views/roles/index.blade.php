{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
@extends('layouts.users')
@section('title', 'Roles & Permissions')

@section('content')
<div class="row g-4">
    {{-- Roles list --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-shield-lock me-1"></i> Roles &amp; Permissions</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Role</th><th>Permissions</th><th class="text-center">Users</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>
                                <div class="fw-semibold text-capitalize">{{ $role->role_name }}
                                    @if(\App\Support\Block1\Access::isProtectedRole($role))<i class="bi bi-lock-fill text-muted small" title="System role"></i>@endif
                                </div>
                                <small class="text-muted">{{ $role->description }}</small>
                            </td>
                            <td>
                                @forelse($permissions[$role->role_id] as $permission)
                                    <span class="badge bg-light text-dark border mb-1">{{ $permission }}</span>
                                @empty
                                    <span class="text-muted small">None</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                <a href="{{ route('users.index', ['role_id' => $role->role_id]) }}" class="badge bg-primary rounded-pill text-decoration-none">{{ ($userCounts[$role->role_id] ?? 0) }}</a>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                @unless(\App\Support\Block1\Access::isProtectedRole($role))
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline"
                                          onsubmit="return confirm('Delete the role {{ $role->role_name }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete" @disabled(($userCounts[$role->role_id] ?? 0) > 0)><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add role --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold"><i class="bi bi-plus-circle me-1"></i> Add role</div>
            <div class="card-body">
                <form method="POST" action="{{ route('roles.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Role name <span class="text-danger">*</span></label>
                        <input type="text" name="role_name" id="role_name" required minlength="3" maxlength="50" pattern="[a-z_]{3,50}"
                               placeholder="e.g. exam_affairs" value="{{ old('role_name') }}"
                               class="form-control @error('role_name') is-invalid @enderror">
                        <div class="invalid-feedback">@error('role_name'){{ $message }}@else 3-50 lowercase letters or underscores.@enderror</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" name="description" id="description" maxlength="255" value="{{ old('description') }}"
                               class="form-control @error('description') is-invalid @enderror">
                        <div class="invalid-feedback">@error('description'){{ $message }}@else Maximum 255 characters.@enderror</div>
                    </div>
                    @include('roles._permissions', ['checked' => old('permissions', []), 'prefix' => 'new'])
                    <button class="btn btn-primary w-100 mt-2"><i class="bi bi-plus-lg me-1"></i> Add role</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Bootstrap client-side validation for role forms.
    $('form.needs-validation').on('submit', function (e) {
        if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
        $(this).addClass('was-validated');
    });
</script>
@endpush
