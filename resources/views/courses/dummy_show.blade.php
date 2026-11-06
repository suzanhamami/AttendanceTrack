@extends('layouts.app')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">{{ $course['course_name'] }} ({{ $course['course_code'] }})</h5>
        <span class="badge {{ $course['status'] === 'active' ? 'bg-success' : 'bg-secondary' }}">
            {{ ucfirst($course['status']) }}
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <strong>Department:</strong> {{ $course['department'] }}
            </div>
            <div class="col-sm-6">
                <strong>Instructor:</strong> {{ $course['instructor'] }}
            </div>
            <div class="col-sm-6">
                <strong>Credit Hours:</strong> {{ $course['credit_hours'] }} Hours
            </div>
            <div class="col-sm-6">
                <strong>Academic Year:</strong> {{ $course['academic_year'] }}
            </div>
            <div class="col-sm-6">
                <strong>Semester:</strong> {{ $course['semester'] }}
            </div>
            <div class="col-sm-6">
                <strong>Prerequisite:</strong> {{ $course['prerequisite'] }}
            </div>
            <div class="col-12 mt-3">
                <strong>Description:</strong>
                <p class="text-muted mt-1">{{ $course['description'] }}</p>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white text-end">
        <a href="{{ route('dummy.courses.index') }}" class="btn btn-secondary btn-sm">Back to Dummy List</a>
        <a href="{{ route('dummy.courses.edit', $course['id']) }}" class="btn btn-primary btn-sm">Edit Dummy Course</a>
    </div>
</div>
@endsection