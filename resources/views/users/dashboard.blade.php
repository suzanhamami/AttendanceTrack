{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
{{-- Block 1 home page. The dashboard buttons depend on the logged-in user's permissions. --}}
@extends('layouts.users')
@section('title', 'Dashboard')

@section('content')
@php($me = auth()->user())

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-0">Users &amp; Permissions Management</h4>
        <small class="text-muted">Welcome, {{ $me->full_name }} &middot; {{ ucfirst($me->role->role_name ?? '') }}</small>
    </div>
</div>

{{-- Statistics --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card shadow-sm border-0 h-100"><div class="card-body d-flex align-items-center">
            <i class="bi bi-people-fill text-primary me-3"></i>
            <div><div class="text-muted small">Total users</div><div class="fs-3 fw-bold">{{ $totalUsers }}</div></div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card shadow-sm border-0 h-100"><div class="card-body d-flex align-items-center">
            <i class="bi bi-person-check-fill text-success me-3"></i>
            <div><div class="text-muted small">Active accounts</div><div class="fs-3 fw-bold">{{ $activeUsers }}</div></div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card shadow-sm border-0 h-100"><div class="card-body d-flex align-items-center">
            <i class="bi bi-person-x-fill text-secondary me-3"></i>
            <div><div class="text-muted small">Inactive accounts</div><div class="fs-3 fw-bold">{{ $inactiveUsers }}</div></div>
        </div></div>
    </div>
</div>

{{-- Dashboard buttons (shown by permission) --}}
<div class="row g-3 mb-4">
    @if(\App\Support\Block1\Access::has($me, 'users.create'))
    <div class="col-6 col-md-3">
        <a href="{{ route('users.create') }}" class="btn btn-primary w-100 dash-btn d-flex flex-column justify-content-center">
            <i class="bi bi-person-plus fs-2"></i> Add User
        </a>
    </div>
    @endif
    <div class="col-6 col-md-3">
        <a href="{{ route('users.index') }}" class="btn btn-outline-primary w-100 dash-btn d-flex flex-column justify-content-center bg-white">
            <i class="bi bi-search fs-2"></i> Search &amp; View Users
        </a>
    </div>
    @if(\App\Support\Block1\Access::has($me, 'users.status'))
    <div class="col-6 col-md-3">
        <a href="{{ route('users.index', ['status' => 'inactive']) }}" class="btn btn-outline-warning w-100 dash-btn d-flex flex-column justify-content-center bg-white">
            <i class="bi bi-toggle-on fs-2"></i> Change Account Status
        </a>
    </div>
    @endif
    @if(\App\Support\Block1\Access::has($me, 'roles.manage'))
    <div class="col-6 col-md-3">
        <a href="{{ route('roles.index') }}" class="btn btn-outline-dark w-100 dash-btn d-flex flex-column justify-content-center bg-white">
            <i class="bi bi-shield-lock fs-2"></i> Roles &amp; Permissions
        </a>
    </div>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-diagram-3 me-1"></i> Users per role</div>
            <ul class="list-group list-group-flush">
                @foreach($roles as $role)
                    <a href="{{ route('users.index', ['role_id' => $role->role_id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        <span class="text-capitalize">{{ $role->role_name }}</span>
                        <span class="badge bg-primary rounded-pill">{{ ($userCounts[$role->role_id] ?? 0) }}</span>
                    </a>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-clock-history me-1"></i> Latest accounts</div>
            <ul class="list-group list-group-flush">
                @forelse($latestUsers as $user)
                    <a href="{{ route('users.show', $user) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        @include('users._avatar', ['user' => $user])
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $user->full_name }}</div>
                            <small class="text-muted">{{ '@' . $user->username }} &middot; {{ $user->role->role_name ?? '-' }}</small>
                        </div>
                        <small class="text-muted">{{ $user->created_at?->diffForHumans() }}</small>
                    </a>
                @empty
                    <li class="list-group-item text-muted">No users yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
