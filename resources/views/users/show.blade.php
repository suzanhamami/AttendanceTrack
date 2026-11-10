{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
{{-- Full user details with his activity in the system. --}}
@extends('layouts.users')
@section('title', $user->full_name)

@section('content')
@php($me = auth()->user())
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item active">{{ $user->full_name }}</li>
    </ol>
</nav>

<div class="row g-4">
    {{-- Profile card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body py-4">
                @include('users._avatar', ['user' => $user, 'class' => 'avatar-lg fs-1 mb-3'])
                <h5 class="fw-bold mb-0">{{ $user->full_name }}</h5>
                <div class="text-muted mb-2">{{ '@' . $user->username }}</div>
                <span class="badge bg-primary text-capitalize">{{ $user->role->role_name ?? '-' }}</span>
                @include('users._status_badge', ['status' => $user->status])

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                    @if(\App\Support\Block1\Access::has($me, 'users.edit') && (!\App\Support\Block1\Access::isPrivileged($user) || \App\Support\Block1\Access::isPrivileged($me)))
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
                    @endif
                    @if(\App\Support\Block1\Access::has($me, 'users.status') && !$user->is($me))
                        <form method="POST" action="{{ route('users.toggle-status', $user) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $user->status === 'active' ? 'btn-warning' : 'btn-success' }}">
                                <i class="bi bi-toggle-{{ $user->status === 'active' ? 'off' : 'on' }} me-1"></i>
                                {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    @endif
                    @if(\App\Support\Block1\Access::has($me, 'users.delete') && !$user->is($me))
                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                              onsubmit="return confirm('Delete this user? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" @disabled(array_sum($linkedCounts) > 0)
                                    title="{{ array_sum($linkedCounts) > 0 ? 'Linked to lectures / courses / sections' : 'Delete' }}">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Personal information --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold"><i class="bi bi-person-vcard me-1"></i> Personal information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Email</dt>        <dd class="col-sm-8">{{ $user->email }}</dd>
                    <dt class="col-sm-4">Phone</dt>        <dd class="col-sm-8">{{ $user->phone ?? '-' }}</dd>
                    <dt class="col-sm-4">Birth date</dt>
                    <dd class="col-sm-8">@php($birth = \App\Support\Block1\Access::birthDate($user)){{ $birth?->format('Y-m-d') ?? '-' }} @if($birth)<small class="text-muted">({{ $birth->age }} years)</small>@endif</dd>
                    <dt class="col-sm-4">Department</dt>   <dd class="col-sm-8">{{ \App\Support\Block1\Access::departmentName($user) }}</dd>
                    <dt class="col-sm-4">Role</dt>         <dd class="col-sm-8 text-capitalize">{{ $user->role->role_name ?? '-' }} <small class="text-muted">{{ $user->role->description ?? '' }}</small></dd>
                    <dt class="col-sm-4">Permissions</dt>
                    <dd class="col-sm-8">
                        @forelse($permissions as $permission)
                            <span class="badge bg-light text-dark border">{{ $permission }}</span>
                        @empty
                            <span class="text-muted">No Block 1 permissions</span>
                        @endforelse
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Activity in the system --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold"><i class="bi bi-activity me-1"></i> Activity in the system</div>
            <div class="card-body">
                <ul class="list-unstyled mb-3">
                    <li><i class="bi bi-calendar-plus text-primary me-2"></i>Account created: {{ $user->created_at?->format('Y-m-d H:i') ?? '-' }}</li>
                    <li><i class="bi bi-calendar-check text-primary me-2"></i>Last updated: {{ $user->updated_at?->format('Y-m-d H:i') ?? '-' }}</li>
                    <li><i class="bi bi-clock-history text-primary me-2"></i>Last activity:
                        {{ $lastActivity ? $lastActivity->format('Y-m-d H:i') . ' (' . $lastActivity->diffForHumans() . ')' : 'No recent session' }}</li>
                </ul>

                @if(empty($linkedCounts))
                    <p class="text-muted small mb-0">Courses, sections and lectures are shown here once those blocks are part of the application.</p>
                @else
                    <div class="row g-3">
                        @foreach($linkedCounts as $table => $count)
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-capitalize fw-semibold">{{ $table }}</span>
                                        <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                                    </div>
                                    @foreach($linkedItems[$table] ?? [] as $item)
                                        <div class="small text-muted text-truncate">{{ $item->title }} @isset($item->extra)&middot; {{ $item->extra }}@endisset</div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(array_sum($linkedCounts) > 0)
                        <div class="alert alert-info small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>This user is linked to other records and cannot be deleted. Deactivate the account instead.</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
