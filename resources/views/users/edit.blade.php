{{-- Block 1 - Users, Roles and Permissions Management | Student name / number: ________ --}}
@extends('layouts.users')
@section('title', 'Edit User')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->full_name }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-1"></i> Edit user information</h5></div>
    <div class="card-body">
        @include('users._form')
    </div>
</div>
@endsection
