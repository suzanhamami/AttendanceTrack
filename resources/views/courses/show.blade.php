@extends('layouts.app')

@section('content')
<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">{{ $course->course_name }} ({{ $course->course_code }})</h5>
        <span class="badge {{ $course->status === 'active' ? 'bg-success' : 'bg-danger' }}">
            {{ ucfirst($course->status) }}
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <strong>Department:</strong> {{ $course->department->department_name ?? 'N/A' }}
            </div>
            <div class="col-sm-6">
                <strong>Instructor:</strong> {{ $course->instructor->full_name ?? 'N/A' }}
            </div>
            <div class="col-sm-6">
                <strong>Credit Hours:</strong> {{ $course->credit_hours }} Hours
            </div>
            <div class="col-sm-6">
                <strong>Academic Year:</strong> {{ $course->academic_year }}
            </div>
            <div class="col-sm-6">
                <strong>Semester:</strong> {{ $course->semester->semester_name ?? 'N/A' }}
            </div>
            <div class="col-sm-6">
                <strong>Prerequisite:</strong> {{ $course->prerequisite->course_name ?? 'None' }}
            </div>
            <div class="col-12 mt-3">
                <strong>Description:</strong>
                <p class="text-muted mt-1">{{ $course->description ?? 'No description provided.' }}</p>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white text-end">
        <a href="{{ route('courses.index') }}" class="btn btn-secondary btn-sm">Back to Courses</a>
        <a href="{{ route('courses.edit', $course->course_id) }}" class="btn btn-primary btn-sm">Edit Course</a>
    </div>
</div>
@endsection